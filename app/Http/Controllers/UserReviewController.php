<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use App\Models\UserReview;
use App\Notifications\ActivityNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserReviewController extends Controller
{
    /**
     * หน้า "รีวิวเพื่อนร่วมกิจกรรม" เปิดให้คนที่ไปจริง และผู้เขียนที่ยังมีรีวิวเดิมให้จัดการ
     * Admin เปิดดูได้อย่างเดียว พร้อมลบรีวิวที่ไม่เหมาะสม
     */
    public function index(Request $request, Activity $activity): View
    {
        $me = $request->user();
        // กิจกรรมที่ถูกซ่อน คนอื่นมองไม่เห็นเหมือนหน้ากิจกรรม
        abort_if($activity->isHidden() && $me->id !== $activity->user_id && ! $me->isAdmin(), 404);

        $activity->load('user');
        $attendees = $activity->attendees();
        $isAttendee = $attendees->contains('id', $me->id);
        $myReviews = $activity->userReviews()->with('reviewee')->where('reviewer_id', $me->id)->get()->keyBy('reviewee_id');
        abort_unless($isAttendee || $myReviews->isNotEmpty() || $me->isAdmin(), 403);

        // เก็บรีวิวเดิมให้เจ้าของแก้ไข/ลบได้ แม้ภายหลังแก้คนใดคนหนึ่งเป็นขาด
        $people = $attendees->reject(fn (User $user) => $user->id === $me->id)
            ->merge($myReviews->pluck('reviewee'))->unique('id')->values();
        $closedReason = $activity->userReviewClosedReason();

        // Admin (ที่ไม่ได้ไปร่วม) ดูรีวิวที่แต่ละคนได้รับในกิจกรรมนี้ เพื่อตรวจและลบรีวิวที่ไม่เหมาะสม
        $receivedByPerson = $me->isAdmin() && ! $isAttendee
            ? $activity->userReviews()->with('reviewer')->latest()->get()->groupBy('reviewee_id')
            : collect();

        return view('activities.people', compact('activity', 'people', 'myReviews', 'closedReason', 'isAttendee', 'receivedByPerson'));
    }

    /** ให้คะแนนเพื่อนร่วมกิจกรรม (ผู้จัด↔ผู้เข้าร่วม และผู้เข้าร่วม↔ผู้เข้าร่วม) */
    public function store(Request $request, Activity $activity, User $user): RedirectResponse
    {
        $validated = $this->validateReview($request);
        $me = $request->user();

        // ตรวจสิทธิ์ที่เซิร์ฟเวอร์เสมอ แม้หน้าเว็บจะซ่อนฟอร์มแล้ว
        $blockReason = $activity->userReviewBlockReason($me, $user);
        if ($blockReason !== null) {
            return back()->with('error', $blockReason);
        }

        $review = new UserReview($validated);
        $review->activity()->associate($activity);
        $review->reviewer()->associate($me);
        $review->reviewee()->associate($user);
        $review->reviewee_role = $user->id === $activity->user_id ? 'host' : 'participant';
        try {
            $review->save();
        } catch (UniqueConstraintViolationException) {
            return back()->with('error', 'คุณรีวิวผู้ใช้นี้ในกิจกรรมนี้ไปแล้ว');
        }

        // แจ้งผู้ถูกรีวิว ลิงก์ไปแท็บรีวิวที่ได้รับในโปรไฟล์ของตัวเอง (URL แบบ relative)
        $user->notify(new ActivityNotification(
            "{$me->name} ให้คะแนนคุณ {$review->rating} ดาว จากกิจกรรม '{$activity->title}'",
            route('users.show', ['user' => $user, 'tab' => $review->reviewee_role], false).'#received-reviews',
            'review'
        ));

        return back()->with('success', 'ส่งรีวิว '.$user->name.' เรียบร้อยแล้ว');
    }

    /** แก้ไขรีวิวของตัวเอง (route ใช้ scopeBindings รีวิวจึงเป็นของกิจกรรมนี้แน่นอน) */
    public function update(Request $request, Activity $activity, UserReview $userReview): RedirectResponse
    {
        abort_if($userReview->reviewer_id !== $request->user()->id, 403);
        if ($activity->isHidden()) {
            return back()->with('error', 'กิจกรรมนี้ถูกซ่อนโดยผู้ดูแลระบบ จึงแก้ไขรีวิวไม่ได้');
        }

        $userReview->fill($this->validateReview($request));
        $userReview->save();

        return back()->with('success', 'แก้ไขรีวิวเรียบร้อยแล้ว');
    }

    /** ลบรีวิวของตัวเอง (Admin ลบรีวิวคนอื่นผ่าน Admin\UserReviewController ที่ต้องใส่เหตุผล) */
    public function destroy(Request $request, Activity $activity, UserReview $userReview): RedirectResponse
    {
        abort_if($userReview->reviewer_id !== $request->user()->id, 403);

        $userReview->delete();

        if (! $activity->isAttendee($request->user()) && ! $request->user()->isAdmin()) {
            return redirect()->route('activities.show', $activity)->with('success', 'ลบรีวิวเรียบร้อยแล้ว');
        }

        return back()->with('success', 'ลบรีวิวเรียบร้อยแล้ว');
    }

    /**
     * กฎเดียวกันทั้งตอนสร้างและแก้ไข (เหมือนรีวิวกิจกรรม)
     *
     * @return array{rating: int, comment?: string|null}
     */
    private function validateReview(Request $request): array
    {
        /** @var array{rating: int, comment?: string|null} */
        return $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ], [
            'rating.required' => 'กรุณาเลือกคะแนน',
            'rating.min' => 'คะแนนต้องอยู่ระหว่าง 1-5',
            'rating.max' => 'คะแนนต้องอยู่ระหว่าง 1-5',
            'comment.max' => 'ความคิดเห็นต้องไม่เกิน 1000 ตัวอักษร',
        ]);
    }
}

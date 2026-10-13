<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Review;
use App\Notifications\ActivityNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /** ส่วนที่ 5: ผู้เข้าร่วมจริงให้คะแนนกิจกรรมหลังจบ */
    public function store(Request $request, Activity $activity): RedirectResponse
    {
        $validated = $this->validateReview($request);

        // ตรวจสิทธิ์ที่เซิร์ฟเวอร์เสมอ แม้หน้าเว็บจะซ่อนฟอร์มแล้ว
        $blockReason = $activity->reviewBlockReason($request->user());
        if ($blockReason !== null) {
            return back()->with('error', $blockReason);
        }

        $review = new Review($validated);
        $review->activity()->associate($activity);
        $review->user()->associate($request->user());
        $review->save();

        // แจ้งผู้จัดว่ามีรีวิวใหม่ ผ่านระบบแจ้งเตือนของส่วนที่ 4
        $activity->user->notify(new ActivityNotification(
            "มีรีวิวใหม่ {$review->rating} ดาว ในกิจกรรม '{$activity->title}'",
            route('activities.show', $activity, false).'#reviews',
            'review'
        ));

        return back()->with('success', 'ขอบคุณสำหรับรีวิว!');
    }

    /** แก้ไขรีวิวของตัวเอง (route ใช้ scopeBindings รีวิวจึงเป็นของกิจกรรมนี้แน่นอน) */
    public function update(Request $request, Activity $activity, Review $review): RedirectResponse
    {
        abort_if($review->user_id !== $request->user()->id, 403);
        if ($activity->isHidden()) {
            return back()->with('error', 'กิจกรรมนี้ถูกซ่อนโดยผู้ดูแลระบบ จึงแก้ไขรีวิวไม่ได้');
        }

        $review->fill($this->validateReview($request));
        $review->save();

        return back()->with('success', 'แก้ไขรีวิวเรียบร้อยแล้ว');
    }

    /** ลบรีวิวของตัวเอง (Admin ลบรีวิวคนอื่นผ่าน Admin\ReviewController ที่ต้องใส่เหตุผล) */
    public function destroy(Request $request, Activity $activity, Review $review): RedirectResponse
    {
        abort_if($review->user_id !== $request->user()->id, 403);

        $review->delete();

        return back()->with('success', 'ลบรีวิวเรียบร้อยแล้ว');
    }

    /**
     * กฎเดียวกันทั้งตอนสร้างและแก้ไข
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

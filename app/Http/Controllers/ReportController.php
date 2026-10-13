<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Report;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * ส่วนที่ 5: รายงานกิจกรรม หรือผู้ใช้ที่เกี่ยวข้องกับกิจกรรม (ผู้จัด/สมาชิก)
     * target = "activity" หรือ "user:{id}"
     */
    public function store(Request $request, Activity $activity): RedirectResponse
    {
        $this->abortIfHiddenFromViewer($request, $activity);

        $validated = $request->validate([
            'target' => ['required', 'string', 'regex:/^(activity|user:\d+)$/'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'target.required' => 'กรุณาเลือกสิ่งที่ต้องการรายงาน',
            'target.regex' => 'สิ่งที่ต้องการรายงานไม่ถูกต้อง',
            'reason.required' => 'กรุณาระบุเหตุผลในการรายงาน',
            'reason.min' => 'เหตุผลต้องมีอย่างน้อย 5 ตัวอักษร',
            'reason.max' => 'เหตุผลต้องไม่เกิน 1000 ตัวอักษร',
        ]);

        $me = $request->user();

        if ($validated['target'] === 'activity') {
            if ($activity->user_id === $me->id) {
                return back()->with('error', 'ไม่สามารถรายงานกิจกรรมของตัวเองได้');
            }
            $targetType = 'activity';
            $targetId = $activity->id;
        } else {
            $targetId = (int) substr($validated['target'], 5);
            if ($targetId === $me->id) {
                return back()->with('error', 'ไม่สามารถรายงานตัวเองได้');
            }
            // รายงานได้เฉพาะผู้จัดหรือสมาชิกที่ได้รับอนุมัติของกิจกรรมนี้
            $related = $targetId === $activity->user_id
                || $activity->approvedParticipants()->where('user_id', $targetId)->exists();
            if (! $related) {
                return back()->with('error', 'ผู้ใช้นี้ไม่ได้เกี่ยวข้องกับกิจกรรมนี้');
            }
            $targetType = 'user';
        }

        return $this->createReport($me, $targetType, $targetId, $validated['reason']);
    }

    /**
     * รายงานรีวิวที่ไม่เหมาะสม (route ใช้ scopeBindings รีวิวต้องเป็นของกิจกรรมใน URL)
     * ใช้ชื่อช่อง review_reason แยกจากฟอร์มรายงานหลัก เพื่อให้ error เปิดฟอร์มถูกอัน
     */
    public function storeReview(Request $request, Activity $activity, Review $review): RedirectResponse
    {
        $this->abortIfHiddenFromViewer($request, $activity);

        $validated = $request->validate([
            'review_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'review_reason.required' => 'กรุณาระบุเหตุผลในการรายงาน',
            'review_reason.min' => 'เหตุผลต้องมีอย่างน้อย 5 ตัวอักษร',
            'review_reason.max' => 'เหตุผลต้องไม่เกิน 1000 ตัวอักษร',
        ]);

        $me = $request->user();
        if ($review->user_id === $me->id) {
            return back()->with('error', 'ไม่สามารถรายงานรีวิวของตัวเองได้');
        }

        return $this->createReport($me, 'review', $review->id, $validated['review_reason']);
    }

    /** กิจกรรมที่ถูกซ่อน คนอื่นมองไม่เห็นในหน้าเว็บ (404) จึงต้องรายงานผ่าน URL ตรงไม่ได้ด้วย */
    private function abortIfHiddenFromViewer(Request $request, Activity $activity): void
    {
        abort_if($activity->isHidden() && $request->user()->id !== $activity->user_id && ! $request->user()->isAdmin(), 404);
    }

    /** บันทึกรายงาน ถ้ารายงานเรื่องเดิมที่ยังรอตรวจอยู่แล้วจะไม่สร้างซ้ำ */
    private function createReport(User $me, string $targetType, int $targetId, string $reason): RedirectResponse
    {
        $duplicate = Report::where('reporter_id', $me->id)
            ->where('target_type', $targetType)
            ->where('target_id', $targetId)
            ->where('status', 'pending')
            ->exists();
        if ($duplicate) {
            return back()->with('error', 'คุณรายงานเรื่องนี้ไปแล้ว กำลังรอผู้ดูแลระบบตรวจสอบ');
        }

        $report = new Report([
            'target_type' => $targetType,
            'target_id' => $targetId,
            'reason' => $reason,
        ]);
        $report->reporter()->associate($me);
        $report->save();

        return back()->with('success', 'ส่งรายงานเรียบร้อยแล้ว ผู้ดูแลระบบจะตรวจสอบโดยเร็ว');
    }
}

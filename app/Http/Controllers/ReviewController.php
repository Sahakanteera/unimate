<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /** ส่วนที่ 5: ผู้เข้าร่วมจริงให้คะแนนกิจกรรมหลังจบ */
    public function store(Request $request, Activity $activity): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ], [
            'rating.required' => 'กรุณาเลือกคะแนน',
            'rating.min' => 'คะแนนต้องอยู่ระหว่าง 1-5',
            'rating.max' => 'คะแนนต้องอยู่ระหว่าง 1-5',
            'comment.max' => 'ความคิดเห็นต้องไม่เกิน 1000 ตัวอักษร',
        ]);

        // ตรวจสิทธิ์ที่เซิร์ฟเวอร์เสมอ แม้หน้าเว็บจะซ่อนฟอร์มแล้ว
        $blockReason = $activity->reviewBlockReason($request->user());
        if ($blockReason !== null) {
            return back()->with('error', $blockReason);
        }

        $review = new Review($validated);
        $review->activity()->associate($activity);
        $review->user()->associate($request->user());
        $review->save();

        return back()->with('success', 'ขอบคุณสำหรับรีวิว!');
    }
}

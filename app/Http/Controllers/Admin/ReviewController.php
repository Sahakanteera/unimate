<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /** ส่วนที่ 5: Admin ลบรีวิวที่ไม่เหมาะสม ต้องมีเหตุผล บันทึกประวัติ และแจ้งผู้เขียนรีวิว */
    public function destroy(Request $request, Review $review): RedirectResponse
    {
        $validated = $request->validate([
            'moderation_reason' => ['required', 'string', 'min:3', 'max:1000'],
        ], [
            'moderation_reason.required' => 'กรุณาบันทึกเหตุผลการลบรีวิว',
            'moderation_reason.min' => 'เหตุผลต้องมีอย่างน้อย 3 ตัวอักษร',
        ]);

        $admin = $request->user();

        // ลบพร้อมบันทึกประวัติ แจ้งผู้เขียน และปิดรายงานของรีวิวนี้ที่ยังรอตรวจ (ดู Review::deleteByAdmin)
        DB::transaction(fn () => $review->deleteByAdmin($admin, $validated['moderation_reason']));

        return back()->with('success', 'ลบรีวิวเรียบร้อยแล้ว');
    }
}

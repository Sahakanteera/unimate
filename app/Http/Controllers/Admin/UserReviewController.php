<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserReviewController extends Controller
{
    /** Admin ลบรีวิวผู้ใช้ที่ไม่เหมาะสม ต้องมีเหตุผล บันทึกประวัติ และแจ้งผู้เขียนรีวิว (แบบเดียวกับรีวิวกิจกรรม) */
    public function destroy(Request $request, UserReview $userReview): RedirectResponse
    {
        $validated = $request->validate([
            'moderation_reason' => ['required', 'string', 'min:3', 'max:1000'],
        ], [
            'moderation_reason.required' => 'กรุณาบันทึกเหตุผลการลบรีวิว',
            'moderation_reason.min' => 'เหตุผลต้องมีอย่างน้อย 3 ตัวอักษร',
        ]);

        $admin = $request->user();

        // ลบพร้อมบันทึกประวัติ แจ้งผู้เขียน และปิดรายงานของรีวิวนี้ที่ยังรอตรวจ (ดู UserReview::deleteByAdmin)
        DB::transaction(fn () => $userReview->deleteByAdmin($admin, $validated['moderation_reason']));

        return back()->with('success', 'ลบรีวิวเรียบร้อยแล้ว');
    }
}

<?php

namespace App\Models;

use App\Notifications\ActivityNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    // activity_id/user_id กำหนดที่ Controller ไม่รับจากฟอร์ม
    protected $fillable = ['rating', 'comment'];

    protected function casts(): array
    {
        return ['rating' => 'integer'];
    }

    /** @return BelongsTo<Activity, $this> */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ส่วนที่ 5: Admin ลบรีวิว ใช้ร่วมกันทั้งปุ่มในหน้ากิจกรรมและหน้าตรวจรายงาน
     * บันทึกประวัติ แจ้งผู้เขียนรีวิว และปิดรายงานอื่นของรีวิวนี้ที่ยังรอตรวจ
     * ($report = รายงานที่กำลังดำเนินการอยู่ ผู้เรียกปิดเอง) ควรเรียกภายใน DB::transaction
     */
    public function deleteByAdmin(User $admin, string $reason, ?Report $report = null): void
    {
        ModerationLog::create([
            'admin_id' => $admin->id,
            'report_id' => $report?->id,
            'action' => 'delete_review',
            'target_type' => 'review',
            'target_id' => $this->id,
            'reason' => $reason,
        ]);

        $this->user->notify(new ActivityNotification(
            "ผู้ดูแลระบบลบรีวิวของคุณในกิจกรรม '{$this->activity->title}': {$reason}",
            null,
            'danger'
        ));

        Report::where('target_type', 'review')
            ->where('target_id', $this->id)
            ->where('status', 'pending')
            ->when($report, fn ($q) => $q->whereKeyNot($report->id))
            ->get()
            ->each(fn (Report $other) => $other->close($admin, 'actioned', $reason));

        $this->delete();
    }
}

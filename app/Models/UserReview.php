<?php

namespace App\Models;

use App\Notifications\ActivityNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/**
 * รีวิวคน: ผู้ที่ไปกิจกรรมจริง (ผู้จัด + ผู้เข้าร่วมที่ถูกเช็กว่ามา) ให้คะแนนกันเองหลังกิจกรรมจบ
 * แยกจาก Review ที่เป็นรีวิวกิจกรรม
 */
class UserReview extends Model
{
    // activity_id/reviewer_id/reviewee_id/reviewee_role กำหนดที่ Controller ไม่รับจากฟอร์ม
    protected $fillable = ['rating', 'comment'];

    public const ROLE_LABELS = [
        'host' => 'ผู้จัด',
        'participant' => 'ผู้เข้าร่วม',
    ];

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
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewee_id');
    }

    public function roleLabel(): string
    {
        return self::ROLE_LABELS[$this->reviewee_role] ?? $this->reviewee_role;
    }

    /**
     * คะแนนเฉลี่ยและจำนวนรีวิวที่ได้รับในบทบาทหนึ่ง ของผู้ใช้หลายคนในคำสั่งเดียว (ไม่นับกิจกรรมที่ถูกซ่อน)
     * คืน [user_id => ['rating' => float, 'total' => int]] ผู้ที่ไม่มีรีวิวจะไม่มีในผลลัพธ์
     *
     * @param  array<int, int>  $userIds
     * @param  'host'|'participant'  $role
     * @return Collection<int, array{rating: float, total: int}>
     */
    public static function ratingsFor(array $userIds, string $role): Collection
    {
        return self::query()
            ->whereIn('reviewee_id', $userIds)
            ->where('reviewee_role', $role)
            ->whereHas('activity', fn ($query) => $query->whereNull('hidden_at'))
            ->selectRaw('reviewee_id, avg(rating) as avg_rating, count(*) as total')
            ->groupBy('reviewee_id')
            ->toBase()
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->reviewee_id => ['rating' => (float) $row->avg_rating, 'total' => (int) $row->total]]);
    }

    /**
     * Admin ลบรีวิวคน ใช้แนวเดียวกับ Review::deleteByAdmin
     * บันทึกประวัติ แจ้งผู้เขียนรีวิว และปิดรายงานอื่นของรีวิวนี้ที่ยังรอตรวจ
     * ($report = รายงานที่กำลังดำเนินการอยู่ ผู้เรียกปิดเอง) ควรเรียกภายใน DB::transaction
     */
    public function deleteByAdmin(User $admin, string $reason, ?Report $report = null): void
    {
        ModerationLog::create([
            'admin_id' => $admin->id,
            'report_id' => $report?->id,
            'action' => 'delete_user_review',
            'target_type' => 'user_review',
            'target_id' => $this->id,
            'reason' => $reason,
        ]);

        $this->reviewer->notify(new ActivityNotification(
            "ผู้ดูแลระบบลบรีวิวที่คุณเขียนถึง {$this->reviewee->name} ในกิจกรรม '{$this->activity->title}': {$reason}",
            null,
            'danger'
        ));

        Report::where('target_type', 'user_review')
            ->where('target_id', $this->id)
            ->where('status', 'pending')
            ->when($report, fn ($q) => $q->whereKeyNot($report->id))
            ->get()
            ->each(fn (Report $other) => $other->close($admin, 'actioned', $reason));

        $this->delete();
    }
}

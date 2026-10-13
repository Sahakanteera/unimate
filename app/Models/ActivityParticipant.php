<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class ActivityParticipant extends Model
{
    protected $fillable = [
        'message',
        'status',
        'attendance',
    ];

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

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * สถิติมาตามนัดของผู้ใช้หลายคนในคำสั่งเดียว: total = ได้รับอนุมัติในกิจกรรมที่จบแล้วและผู้จัดเช็กชื่อแล้ว
     * present = ถูกเช็กว่ามา (ไม่นับกิจกรรมที่ยกเลิกหรือถูกซ่อน) ผู้ที่ไม่มีข้อมูลจะไม่มีในผลลัพธ์
     *
     * @param  array<int, int>  $userIds
     * @return Collection<int, array{present: int, total: int}>
     */
    public static function attendanceFor(array $userIds): Collection
    {
        return self::query()
            ->whereIn('user_id', $userIds)
            ->where('status', 'approved')
            ->whereNotNull('attendance')
            ->whereHas('activity', function ($query) {
                $query->where('ends_at', '<=', now())->where('status', '!=', 'cancelled')->whereNull('hidden_at');
            })
            ->selectRaw("user_id, count(*) as total, sum(case when attendance = 'present' then 1 else 0 end) as present")
            ->groupBy('user_id')
            ->toBase()
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->user_id => ['present' => (int) $row->present, 'total' => (int) $row->total]]);
    }
}

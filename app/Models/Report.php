<?php

namespace App\Models;

use App\Notifications\ActivityNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = ['target_type', 'target_id', 'reason'];

    /** ป้ายประเภทสิ่งที่ถูกรายงาน (target_type) */
    public const TARGET_LABELS = [
        'activity' => 'กิจกรรม',
        'user' => 'ผู้ใช้',
        'review' => 'รีวิว',
    ];

    protected function casts(): array
    {
        return ['handled_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /** @return BelongsTo<User, $this> */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /** คืนกิจกรรม ผู้ใช้ หรือรีวิวที่ถูกรายงาน (null ถ้าถูกลบไปแล้ว) */
    public function target(): Activity|User|Review|null
    {
        return match ($this->target_type) {
            'activity' => Activity::find($this->target_id),
            'review' => Review::with(['user', 'activity'])->find($this->target_id),
            default => User::find($this->target_id),
        };
    }

    public function targetLabel(): string
    {
        return self::TARGET_LABELS[$this->target_type] ?? $this->target_type;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /** ปิดรายงาน (actioned/dismissed) บันทึกผู้ดำเนินการ และแจ้งผู้รายงานผ่านระบบแจ้งเตือนของส่วนที่ 4 */
    public function close(User $admin, string $status, string $note): void
    {
        $this->status = $status;
        $this->admin_note = $note;
        $this->handler()->associate($admin);
        $this->handled_at = now();
        $this->save();

        $this->reporter->notify(new ActivityNotification(
            $status === 'actioned'
                ? 'ผู้ดูแลระบบดำเนินการกับรายงานของคุณแล้ว: '.$note
                : 'ผู้ดูแลระบบตรวจสอบรายงานของคุณแล้ว และไม่พบการกระทำผิด: '.$note
        ));
    }
}

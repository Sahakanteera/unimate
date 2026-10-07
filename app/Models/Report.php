<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = ['target_type', 'target_id', 'reason'];

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

    /** คืนกิจกรรมหรือผู้ใช้ที่ถูกรายงาน (null ถ้าถูกลบไปแล้ว) */
    public function target(): Activity|User|null
    {
        return $this->target_type === 'activity'
            ? Activity::find($this->target_id)
            : User::find($this->target_id);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}

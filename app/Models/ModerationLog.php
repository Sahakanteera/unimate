<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModerationLog extends Model
{
    protected $fillable = ['admin_id', 'report_id', 'action', 'target_type', 'target_id', 'reason'];

    public const ACTION_LABELS = [
        'hide_activity' => 'ซ่อนกิจกรรม',
        'unhide_activity' => 'เลิกซ่อนกิจกรรม',
        'suspend_user' => 'ระงับบัญชีผู้ใช้',
        'dismiss' => 'ยกรายงาน (ไม่ดำเนินการ)',
        'delete_review' => 'ลบรีวิว',
        'delete_user_review' => 'ลบรีวิวผู้ใช้',
    ];

    /** @return BelongsTo<User, $this> */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function actionLabel(): string
    {
        return self::ACTION_LABELS[$this->action] ?? $this->action;
    }

    public function targetLabel(): string
    {
        if ($this->target_type === 'review') {
            // รีวิวที่ถูกลบเหลือแค่หมายเลขอ้างอิง (กรณียกรายงาน รีวิวยังอยู่)
            $review = Review::with('user')->find($this->target_id);

            return $review === null
                ? 'รีวิว #'.$this->target_id.' (ลบแล้ว)'
                : 'รีวิวของ '.$review->user->name;
        }
        if ($this->target_type === 'user_review') {
            $userReview = UserReview::with(['reviewer', 'reviewee'])->find($this->target_id);

            return $userReview === null
                ? 'รีวิวผู้ใช้ #'.$this->target_id.' (ลบแล้ว)'
                : 'รีวิวจาก '.$userReview->reviewer->name.' ถึง '.$userReview->reviewee->name;
        }
        if ($this->target_type === 'activity') {
            $name = Activity::find($this->target_id)?->title;
            $prefix = 'กิจกรรม';
        } else {
            $name = User::find($this->target_id)?->name;
            $prefix = 'ผู้ใช้';
        }

        return $prefix.': '.($name ?? '#'.$this->target_id.' (ถูกลบแล้ว)');
    }
}

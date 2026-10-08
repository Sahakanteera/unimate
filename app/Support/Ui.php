<?php

namespace App\Support;

use App\Models\Activity;
use Illuminate\Support\Carbon;

/**
 * ค่าที่หน้าจอใช้ร่วมกัน (ดู docs/ui-design-th.md)
 */
class Ui
{
    /** สีพาสเทลแบบโพสต์อิทของ fastwork ใช้แยกหมวดหมู่และอักษรย่อผู้ใช้ */
    public const TINTS = ['bg-tint-mint', 'bg-tint-aqua', 'bg-tint-lilac', 'bg-tint-coral', 'bg-tint-butter', 'bg-tint-peach'];

    public static function tint(int $seed): string
    {
        return self::TINTS[$seed % count(self::TINTS)];
    }

    /**
     * สถานะกิจกรรมสำหรับแสดงผล: cancelled, ended, ongoing (เริ่มแล้ว ปิดรับคำขอ), full หรือ open
     * ใช้ Carbon::parse แบบเดียวกับ ActivityPolicy เพราะ PHPStan อ่านคอลัมน์เวลาเป็น string
     */
    public static function activityState(Activity $activity, int $approvedCount): string
    {
        return match (true) {
            $activity->status === 'cancelled' => 'cancelled',
            $activity->isEnded() => 'ended',
            Carbon::parse($activity->starts_at)->isPast() => 'ongoing',
            $approvedCount >= $activity->capacity => 'full',
            default => 'open',
        };
    }
}

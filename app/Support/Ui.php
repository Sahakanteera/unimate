<?php

namespace App\Support;

use App\Models\Activity;
use Illuminate\Support\Carbon;

/**
 * ค่าที่หน้าจอใช้ร่วมกัน (ดู docs/ui-design-th.md)
 */
class Ui
{
    /** หมวดหมู่ที่มีภาพประกอบของตัวเอง (ชื่อตาม CategorySeeder) */
    public const CATEGORY_SLUGS = [
        'กีฬา' => 'sports',
        'ติวหนังสือ' => 'tutoring',
        'ท่องเที่ยว' => 'travel',
        'จิตอาสา' => 'volunteer',
        'ดนตรี' => 'music',
        'อื่น ๆ' => 'other',
    ];

    /** ชื่อหมวด → ชื่อไฟล์ภาพ หมวดที่ผู้ดูแลเพิ่มภายหลังใช้ภาพของ "อื่น ๆ" */
    public static function categorySlug(?string $name): string
    {
        return self::CATEGORY_SLUGS[trim($name ?? '')] ?? 'other';
    }

    /** ไอคอนกระดาษ 3 มิติของหมวด ($size 96 หรือ 192 px) */
    public static function categoryIcon(?string $name, int $size = 96): string
    {
        return 'images/web/categories/'.self::categorySlug($name).($size > 96 ? '-192' : '').'.webp';
    }

    /**
     * ภาพถ่ายประกอบของหมวด (ไม่รวมนามสกุลและขนาด) หรือ null ถ้าหมวดนี้ไม่มีภาพของตัวเอง
     * ภาพถ่ายเป็นบรรยากาศของทั้งหมวด จึงใช้เฉพาะหน้ารายละเอียด ไม่ใช้ซ้ำบนการ์ดทุกใบ
     */
    public static function categoryPhoto(?string $name): ?string
    {
        $slug = self::CATEGORY_SLUGS[trim($name ?? '')] ?? null;

        return $slug ? 'images/web/photos/activities/'.$slug : null;
    }

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

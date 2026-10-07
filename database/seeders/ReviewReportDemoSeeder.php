<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * ข้อมูลตัวอย่างสำหรับสาธิตส่วนที่ 5 (รีวิว/รายงาน)
 * รันหลัง UserSeeder: php artisan db:seed --class=ReviewReportDemoSeeder
 */
class ReviewReportDemoSeeder extends Seeder
{
    public function run(): void
    {
        $host = User::where('email', 'student@unimate.ac.th')->firstOrFail();

        // ผู้เข้าร่วมจริง (เช็กชื่อว่ามา) — ใช้รหัสผ่านเดียวกับ UserSeeder
        $attendee = User::updateOrCreate(
            ['email' => 'attendee@unimate.ac.th'],
            ['name' => 'Mint T. (มิ้นท์)', 'student_id' => '653020003-3', 'password' => Hash::make('password'), 'role' => 'student', 'status' => 'active']
        );

        $category = Category::firstOrCreate(['name' => 'กีฬา']);

        // กิจกรรมที่จบแล้ว เพื่อให้รีวิวได้ทันที
        $activity = Activity::firstOrNew(['title' => '[Demo] บาสเย็นวันศุกร์ (จบแล้ว)']);
        $activity->fill([
            'category_id' => $category->id,
            'description' => 'กิจกรรมตัวอย่างสำหรับสาธิตระบบรีวิวและรายงาน',
            'location' => 'สนามบาสกลาง',
            'starts_at' => now()->subDay()->setTime(17, 0),
            'ends_at' => now()->subDay()->setTime(19, 0),
            'capacity' => 10,
        ]);
        $activity->user()->associate($host);
        $activity->status = 'published';
        $activity->save();

        $participant = ActivityParticipant::firstOrNew(['activity_id' => $activity->id, 'user_id' => $attendee->id]);
        $participant->activity()->associate($activity);
        $participant->user()->associate($attendee);
        $participant->status = 'approved';
        $participant->attendance = 'present';
        $participant->save();
    }
}

<?php

use App\Models\Activity;
use App\Models\Report;
use App\Models\User;
use App\Models\UserReview;
use Database\Seeders\PresentationDemoSeeder;

test('seeder เดโมสร้างข้อมูลครบทุกระบบ และหน้าหลักของบัญชีเดโมเปิดได้', function () {
    $this->seed(PresentationDemoSeeder::class);

    $student = User::where('email', 'student@unimate.ac.th')->firstOrFail();
    $admin = User::where('email', 'admin@unimate.ac.th')->firstOrFail();
    $host = User::where('email', 'host@unimate.ac.th')->firstOrFail();

    expect(Report::where('status', 'pending')->count())->toBe(5);
    expect(Report::where('status', 'pending')->where('target_type', 'user_review')->count())->toBe(1);
    expect(Activity::whereNotNull('hidden_at')->count())->toBe(1);
    expect($student->unreadNotifications()->count())->toBe(5);

    $badminton = $student->activities()->where('title', 'แบดมินตันหลังเลิกเรียน มือใหม่ยินดีต้อนรับ')->firstOrFail();
    $this->actingAs($student)->get(route('activities.requests', $badminton))->assertOk()->assertSee('แพรว กมลชนก');
    $this->get(route('my-activities.index'))->assertOk()->assertSee('ให้คะแนน');
    $this->get(route('notifications.index'))->assertOk();
    $this->get(route('users.show', $host))->assertOk()->assertSee('มายด์ ศิริพร');

    // รีวิวคนที่ค้างให้ student@ กดสด: ในฐานะผู้เข้าร่วม (เดินป่า) และในฐานะผู้จัด (บาสวันพุธ)
    $hike = Activity::where('title', 'เดินป่าน้ำตกตาดฟ้า วันเดียวจบ')->firstOrFail();
    $basketball = $student->activities()->where('title', 'บาสเย็นวันพุธ ขาดอีก 3')->firstOrFail();
    $this->get(route('activities.show', $hike))->assertOk()->assertViewHas('peopleToReview', 4);
    $this->get(route('activities.show', $basketball))->assertOk()->assertViewHas('peopleToReview', 4);
    $this->get(route('activities.people', $hike))->assertOk()->assertSee('ส่งรีวิวผู้ใช้')->assertSee('มายด์ ศิริพร');
    expect(UserReview::where('reviewer_id', $student->id)->whereIn('activity_id', [$hike->id, $basketball->id])->count())->toBe(0);

    // คะแนนในโปรไฟล์มาจากรีวิวคน: ผู้จัด 4.8 (5 รีวิว) ผู้เข้าร่วม 5.0 (3 รีวิว) มาตามนัด 2/3
    $profile = $this->get(route('users.show', $student))->assertOk();
    expect($profile->viewData('stats')['host_rating'])->toBe(['rating' => 4.8, 'total' => 5]);
    expect($profile->viewData('stats')['participant_rating'])->toBe(['rating' => 5.0, 'total' => 3]);
    expect($profile->viewData('stats')['attendance'])->toBe(['present' => 2, 'total' => 3]);

    $this->actingAs($admin)->get(route('admin.reports.index'))->assertOk()->assertSee('รับทำการบ้าน')->assertSee('หนวกหูชะมัด');
    $this->get(route('admin.insights.index'))->assertOk();
});

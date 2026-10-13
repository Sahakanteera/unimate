<?php

use App\Models\Activity;
use App\Models\Report;
use App\Models\User;
use Database\Seeders\PresentationDemoSeeder;

test('seeder เดโมสร้างข้อมูลครบทุกระบบ และหน้าหลักของบัญชีเดโมเปิดได้', function () {
    $this->seed(PresentationDemoSeeder::class);

    $student = User::where('email', 'student@unimate.ac.th')->firstOrFail();
    $admin = User::where('email', 'admin@unimate.ac.th')->firstOrFail();
    $host = User::where('email', 'host@unimate.ac.th')->firstOrFail();

    expect(Report::where('status', 'pending')->count())->toBe(4);
    expect(Activity::whereNotNull('hidden_at')->count())->toBe(1);
    expect($student->unreadNotifications()->count())->toBe(5);

    $badminton = $student->activities()->where('title', 'แบดมินตันหลังเลิกเรียน มือใหม่ยินดีต้อนรับ')->firstOrFail();
    $this->actingAs($student)->get(route('activities.requests', $badminton))->assertOk()->assertSee('แพรว กมลชนก');
    $this->get(route('my-activities.index'))->assertOk()->assertSee('ให้คะแนน');
    $this->get(route('notifications.index'))->assertOk();
    $this->get(route('users.show', $host))->assertOk()->assertSee('มายด์ ศิริพร');

    $this->actingAs($admin)->get(route('admin.reports.index'))->assertOk()->assertSee('รับทำการบ้าน');
    $this->get(route('admin.insights.index'))->assertOk();
});

<?php

use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\Category;
use App\Models\Review;
use App\Models\User;

// ──────────────── Helpers (ชื่อขึ้นต้น pf เพื่อไม่ชนกับ helper ในไฟล์ทดสอบอื่น) ────────────────

function pfUser(string $email, string $role = 'student', string $status = 'active'): User
{
    return User::create([
        // ชื่อไม่มีอีเมลปน เพื่อให้ตรวจได้ว่าหน้าโปรไฟล์ไม่แสดงอีเมล
        'name' => 'ผู้ใช้ '.strtok($email, '@'),
        'student_id' => 'S'.crc32($email),
        'email' => $email,
        'password' => 'password',
        'role' => $role,
        'status' => $status,
        'bio' => 'ชอบเล่นกีฬา',
    ]);
}

function pfActivity(User $host, string $title, bool $ended = true): Activity
{
    $activity = new Activity([
        'category_id' => Category::firstOrCreate(['name' => 'กีฬา'])->id,
        'title' => $title,
        'description' => 'ทดสอบโปรไฟล์',
        'location' => 'สนามกลาง',
        'starts_at' => $ended ? now()->subDays(2) : now()->addDays(2),
        'ends_at' => $ended ? now()->subDays(2)->addHours(2) : now()->addDays(2)->addHours(2),
        'capacity' => 10,
    ]);
    $activity->user()->associate($host);
    $activity->status = 'published';
    $activity->save();

    return $activity;
}

function pfJoin(Activity $activity, User $user, string $status = 'approved', ?string $attendance = 'present'): void
{
    $p = new ActivityParticipant;
    $p->activity()->associate($activity);
    $p->user()->associate($user);
    $p->status = $status;
    $p->attendance = $attendance;
    $p->save();
}

function pfReview(Activity $activity, User $user, int $rating, ?string $comment = null): Review
{
    $review = new Review(['rating' => $rating, 'comment' => $comment]);
    $review->activity()->associate($activity);
    $review->user()->associate($user);
    $review->save();

    return $review;
}

// ──────────────── โปรไฟล์สาธารณะ ────────────────

test('guest ถูกพาไปหน้าเข้าสู่ระบบ', function () {
    $user = pfUser('pf-guest@test.com');

    $this->get(route('users.show', $user))->assertRedirect(route('login'));
});

test('ดูโปรไฟล์คนอื่นได้ โดยไม่เห็นอีเมลและรหัสนักศึกษา', function () {
    $host = pfUser('pf-host@test.com');
    $viewer = pfUser('pf-viewer@test.com');
    pfActivity($host, 'ปั่นจักรยานรอบมอ');

    $this->actingAs($viewer)->get(route('users.show', $host))
        ->assertOk()
        ->assertSee($host->name)
        ->assertSee('ชอบเล่นกีฬา')
        ->assertSee('ปั่นจักรยานรอบมอ')
        ->assertSee('คะแนนจากการจัดกิจกรรม')
        ->assertDontSee('pf-host@test.com')
        ->assertDontSee($host->student_id)
        ->assertViewHas('isSelf', false); // เมนูบัญชีใน nav มีลิงก์ "แก้ไขโปรไฟล์" ทุกหน้า จึงตรวจจากค่าที่ส่งให้ view
});

test('โปรไฟล์ตัวเองมีปุ่มแก้ไขโปรไฟล์', function () {
    $user = pfUser('pf-self@test.com');

    $this->actingAs($user)->get(route('users.show', $user))
        ->assertOk()->assertViewHas('isSelf', true)->assertSee('แก้ไขโปรไฟล์');
});

test('คะแนนผู้จัดเฉลี่ยจากกิจกรรมที่จัด และไม่นับกิจกรรมที่ถูกซ่อน', function () {
    $host = pfUser('pf-host2@test.com');
    $a = pfUser('pf-a@test.com');
    $b = pfUser('pf-b@test.com');
    $first = pfActivity($host, 'กิจกรรมแรก');
    $second = pfActivity($host, 'กิจกรรมที่สอง');
    $hidden = pfActivity($host, 'กิจกรรมที่ถูกซ่อน');
    $hidden->hidden_at = now();
    $hidden->hidden_reason = 'ทดสอบ';
    $hidden->save();

    pfReview($first, $a, 5, 'จัดดีมาก');
    pfReview($first, $b, 4);
    pfReview($second, $a, 3);
    pfReview($hidden, $b, 1, 'รีวิวในกิจกรรมที่ซ่อน');

    // (5 + 4 + 3) / 3 = 4.0 จาก 3 รีวิว
    $this->actingAs($a)->get(route('users.show', $host))
        ->assertOk()
        ->assertSee('4.0')
        ->assertSee('จาก 3 รีวิว')
        ->assertSee('จัดดีมาก')
        ->assertDontSee('รีวิวในกิจกรรมที่ซ่อน')
        ->assertDontSee('กิจกรรมที่ถูกซ่อน');

    // เจ้าของยังเห็นกิจกรรมที่ถูกซ่อนในประวัติของตัวเอง แต่คะแนนยังไม่นับ
    $this->actingAs($host)->get(route('users.show', $host))
        ->assertOk()->assertSee('กิจกรรมที่ถูกซ่อน')->assertSee('จาก 3 รีวิว');
});

test('ประวัติเข้าร่วมนับเฉพาะที่มาจริงและกิจกรรมจบแล้ว', function () {
    $host = pfUser('pf-host3@test.com');
    $member = pfUser('pf-member@test.com');
    pfJoin(pfActivity($host, 'มาจริงและจบแล้ว'), $member);
    pfJoin(pfActivity($host, 'ขาดนัด'), $member, 'approved', 'absent');
    pfJoin(pfActivity($host, 'ยังรออนุมัติ'), $member, 'pending', null);
    pfJoin(pfActivity($host, 'ยังไม่ถึงวัน', ended: false), $member, 'approved', null);

    $response = $this->actingAs($host)->get(route('users.show', $member))->assertOk();

    expect($response->viewData('stats')['attended'])->toBe(1);
    expect($response->viewData('attended')->pluck('title')->all())->toBe(['มาจริงและจบแล้ว']);
    $response->assertSee('มาจริงและจบแล้ว')->assertDontSee('ขาดนัด')->assertDontSee('ยังไม่ถึงวัน');
});

test('ผู้ใช้ที่ถูกระงับเป็น 404 สำหรับคนทั่วไป แต่ Admin ยังดูได้', function () {
    $suspended = pfUser('pf-suspended@test.com', status: 'suspended');
    $viewer = pfUser('pf-viewer2@test.com');
    $admin = pfUser('pf-admin@test.com', 'admin');

    $this->actingAs($viewer)->get(route('users.show', $suspended))->assertNotFound();
    $this->actingAs($admin)->get(route('users.show', $suspended))->assertOk()->assertSee('ถูกระงับ');
});

test('ชื่อผู้จัดและสมาชิกในหน้ากิจกรรมลิงก์ไปหน้าโปรไฟล์', function () {
    $host = pfUser('pf-host4@test.com');
    $member = pfUser('pf-member2@test.com');
    $activity = pfActivity($host, 'กิจกรรมมีลิงก์', ended: false);
    pfJoin($activity, $member, 'approved', null);

    $this->actingAs($member)->get(route('activities.show', $activity))
        ->assertOk()
        ->assertSee(route('users.show', $host), false)
        ->assertSee(route('users.show', $member), false);
});

<?php

use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\Category;
use App\Models\Review;
use App\Models\User;
use App\Models\UserReview;

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

/** @param 'host'|'participant' $role */
function pfUserReview(Activity $activity, User $reviewer, User $reviewee, string $role, int $rating, ?string $comment = null): UserReview
{
    $review = new UserReview(['rating' => $rating, 'comment' => $comment]);
    $review->activity()->associate($activity);
    $review->reviewer()->associate($reviewer);
    $review->reviewee()->associate($reviewee);
    $review->reviewee_role = $role;
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
        ->assertSee('คะแนนในฐานะผู้จัด')
        ->assertSee('คะแนนในฐานะผู้เข้าร่วม')
        ->assertDontSee('pf-host@test.com')
        ->assertDontSee($host->student_id)
        ->assertViewHas('isSelf', false); // เมนูบัญชีใน nav มีลิงก์ "แก้ไขโปรไฟล์" ทุกหน้า จึงตรวจจากค่าที่ส่งให้ view
});

test('โปรไฟล์ตัวเองมีปุ่มแก้ไขโปรไฟล์', function () {
    $user = pfUser('pf-self@test.com');

    $this->actingAs($user)->get(route('users.show', $user))
        ->assertOk()->assertViewHas('isSelf', true)->assertSee('แก้ไขโปรไฟล์');
});

test('คะแนนผู้จัดเฉลี่ยจากรีวิวคนในฐานะผู้จัด และไม่นับกิจกรรมที่ถูกซ่อน', function () {
    $host = pfUser('pf-host2@test.com');
    $a = pfUser('pf-a@test.com');
    $b = pfUser('pf-b@test.com');
    $first = pfActivity($host, 'กิจกรรมแรก');
    $second = pfActivity($host, 'กิจกรรมที่สอง');
    $hidden = pfActivity($host, 'กิจกรรมที่ถูกซ่อน');
    $hidden->hidden_at = now();
    $hidden->hidden_reason = 'ทดสอบ';
    $hidden->save();

    pfUserReview($first, $a, $host, 'host', 5, 'จัดดีมาก');
    pfUserReview($first, $b, $host, 'host', 4);
    pfUserReview($second, $a, $host, 'host', 3);
    pfUserReview($hidden, $b, $host, 'host', 1, 'รีวิวในกิจกรรมที่ซ่อน');
    // รีวิวกิจกรรม (ตาราง reviews) ไม่ถูกนำมาคิดเป็นคะแนนของคนแล้ว
    pfReview($first, $a, 1, 'รีวิวกิจกรรมไม่ใช่รีวิวคน');

    // (5 + 4 + 3) / 3 = 4.0 จาก 3 รีวิว
    $response = $this->actingAs($a)->get(route('users.show', $host))
        ->assertOk()
        ->assertSee('4.0')
        ->assertSee('จาก 3 รีวิว')
        ->assertSee('จัดดีมาก')
        ->assertDontSee('รีวิวในกิจกรรมที่ซ่อน')
        ->assertDontSee('รีวิวกิจกรรมไม่ใช่รีวิวคน')
        ->assertDontSee('กิจกรรมที่ถูกซ่อน');
    expect($response->viewData('stats')['host_rating'])->toBe(['rating' => 4.0, 'total' => 3]);
    expect($response->viewData('stats')['participant_rating'])->toBeNull();
    expect($response->viewData('tab'))->toBe('host');

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
    // มาตามนัด X/Y: Y นับเฉพาะที่อนุมัติ จบแล้ว และเช็กชื่อแล้ว (มา 1 ขาด 1)
    expect($response->viewData('stats')['attendance'])->toBe(['present' => 1, 'total' => 2]);
    $response->assertSee('ครั้ง');
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

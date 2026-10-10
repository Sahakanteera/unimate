<?php

use App\Models\Activity;
use App\Models\Category;
use App\Models\User;

// ──────────────── Helpers (ชื่อขึ้นต้น ui เพื่อไม่ชนกับ helper ในไฟล์ทดสอบอื่น) ────────────────

function uiUser(string $email, string $role = 'student'): User
{
    return User::create([
        'name' => 'User '.$email,
        'email' => $email,
        'password' => 'password',
        'role' => $role,
        'status' => 'active',
    ]);
}

/** สร้างกิจกรรมโดยตรง (ข้าม validation เวลาในอนาคต) เพื่อกำหนดเวลาเริ่ม/จบได้ตามต้องการ */
function uiActivity(User $host, string $title, DateTimeInterface $startsAt, DateTimeInterface $endsAt): Activity
{
    $activity = new Activity([
        'category_id' => Category::firstOrCreate(['name' => 'กีฬา'])->id,
        'title' => $title,
        'description' => 'ทดสอบหน้าจอ',
        'location' => 'สนามกลาง',
        'starts_at' => $startsAt,
        'ends_at' => $endsAt,
        'capacity' => 5,
    ]);
    $activity->user()->associate($host);
    $activity->status = 'published';
    $activity->save();

    return $activity;
}

test('students land on the activities page after logging in', function () {
    uiUser('ui-login@unimate.test');

    $this->post(route('login'), ['email' => 'ui-login@unimate.test', 'password' => 'password'])
        ->assertRedirect(route('activities.index'));
});

test('the activities list shows upcoming activities before ended ones', function () {
    $host = uiUser('ui-host@unimate.test');
    uiActivity($host, 'กิจกรรมที่จบไปแล้ว', now()->subDays(3), now()->subDays(3)->addHours(2));
    uiActivity($host, 'กิจกรรมที่กำลังจะมาถึง', now()->addDays(3), now()->addDays(3)->addHours(2));

    $this->actingAs(uiUser('ui-viewer@unimate.test'))->get(route('activities.index'))
        ->assertOk()
        ->assertSeeInOrder(['กิจกรรมที่กำลังจะมาถึง', 'กิจกรรมที่จบไปแล้ว']);
});

test('a started activity shows as in progress and offers no join form', function () {
    $activity = uiActivity(uiUser('ui-host2@unimate.test'), 'กำลังเล่นอยู่', now()->subMinutes(30), now()->addHour());

    $this->actingAs(uiUser('ui-viewer2@unimate.test'))->get(route('activities.show', $activity))
        ->assertOk()
        ->assertSee('กำลังดำเนินการ')
        ->assertSee('กิจกรรมเริ่มไปแล้ว จึงปิดรับคำขอเข้าร่วม')
        ->assertDontSee('ส่งคำขอเข้าร่วม');
});

test('owners of a started activity get a shortcut to take attendance', function () {
    $host = uiUser('ui-host3@unimate.test');
    $activity = uiActivity($host, 'จบแล้วต้องเช็กชื่อ', now()->subDay(), now()->subDay()->addHours(2));

    $this->actingAs($host)->get(route('activities.show', $activity))
        ->assertOk()
        ->assertSee('เช็กชื่อผู้เข้าร่วม')
        ->assertSee(route('activities.requests', ['activity' => $activity, 'status' => 'approved']), false);
});

test('error messages stay on screen while success messages hide themselves', function () {
    $user = uiUser('ui-flash@unimate.test');
    $openingTag = fn (string $html, string $id) => preg_match('/<div id="'.$id.'"[^>]*>/', $html, $m) ? $m[0] : '';

    $error = $this->actingAs($user)->withSession(['error' => 'มีบางอย่างผิดพลาด'])->get(route('activities.index'));
    $error->assertSee('มีบางอย่างผิดพลาด');
    expect($openingTag($error->getContent(), 'flash-error'))->not->toBe('')->not->toContain('data-autohide');

    $success = $this->actingAs($user)->withSession(['success' => 'บันทึกแล้ว'])->get(route('activities.index'));
    expect($openingTag($success->getContent(), 'flash-success'))->toContain('data-autohide');
});

test('list and detail pages use the category icon art', function () {
    $activity = uiActivity(uiUser('ui-cover-host@unimate.test'), 'วิ่งรอบอ่างตอนเช้า', now()->addDays(2), now()->addDays(2)->addHours(2));
    $this->actingAs(uiUser('ui-cover-viewer@unimate.test'));

    foreach ([route('activities.index'), route('activities.show', $activity)] as $url) {
        $this->get($url)->assertOk()
            ->assertSee('images/web/categories/sports-192.webp', false)
            ->assertDontSee('images/web/photos/', false);
    }
});

test('categories added later fall back to the generic icon', function () {
    $activity = uiActivity(uiUser('ui-cover-host2@unimate.test'), 'กิจกรรมถ่ายภาพ', now()->addDays(2), now()->addDays(2)->addHours(2));
    $activity->category->update(['name' => 'ถ่ายภาพ']);
    $this->actingAs(uiUser('ui-cover-viewer2@unimate.test'));

    $this->get(route('activities.show', $activity))->assertOk()
        ->assertSee('images/web/categories/other-192.webp', false);
});

test('auth pages show the note board instead of photos', function () {
    $this->get(route('login'))->assertOk()
        ->assertSee('images/web/categories/sports-192.webp', false)
        ->assertDontSee('images/web/photos/', false);
});

test('missing pages show the custom recovery page for guests', function () {
    $this->get('/unimate-page-that-does-not-exist')->assertNotFound()
        ->assertSee('images/web/illustrations/error-404.webp', false)
        ->assertSee('กลับหน้าแรก');
});

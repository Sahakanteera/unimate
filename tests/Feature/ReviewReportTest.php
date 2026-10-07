<?php

use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\Category;
use App\Models\ModerationLog;
use App\Models\Report;
use App\Models\User;

// ──────────────── Helpers (ชื่อขึ้นต้น p5 เพื่อไม่ชนกับ helper ใน ParticipationTest) ────────────────

function p5User(string $email, string $role = 'student'): User
{
    return User::create([
        'name' => 'User '.$email,
        'email' => $email,
        'password' => 'password',
        'role' => $role,
        'status' => 'active',
    ]);
}

/** สร้างกิจกรรมโดยตรง (ข้าม validation เวลาในอนาคต) เพื่อทดสอบกิจกรรมที่จบแล้ว */
function p5Activity(User $host, bool $ended = true): Activity
{
    $category = Category::firstOrCreate(['name' => 'กีฬา']);
    $activity = new Activity([
        'category_id' => $category->id,
        'title' => 'เล่นบาสเย็นนี้',
        'description' => 'หาเพื่อนเล่นบาส',
        'location' => 'สนามบาส',
        'starts_at' => $ended ? now()->subDays(2) : now()->addDays(2),
        'ends_at' => $ended ? now()->subDays(2)->addHours(2) : now()->addDays(2)->addHours(2),
        'capacity' => 10,
    ]);
    $activity->user()->associate($host);
    $activity->status = 'published';
    $activity->save();

    return $activity;
}

function p5Participant(Activity $activity, User $user, string $status = 'approved', ?string $attendance = 'present'): ActivityParticipant
{
    $p = new ActivityParticipant;
    $p->activity()->associate($activity);
    $p->user()->associate($user);
    $p->status = $status;
    $p->attendance = $attendance;
    $p->save();

    return $p;
}

// ──────────────── รีวิว ────────────────

test('ผู้เข้าร่วมจริงรีวิวกิจกรรมที่จบแล้วได้ และหน้ากิจกรรมแสดงคะแนนเฉลี่ย', function () {
    $host = p5User('host@test.com');
    $a = p5User('a@test.com');
    $b = p5User('b@test.com');
    $activity = p5Activity($host);
    p5Participant($activity, $a);
    p5Participant($activity, $b);

    $this->actingAs($a)->post(route('activities.reviews.store', $activity), ['rating' => 5, 'comment' => 'สนุกมาก'])
        ->assertSessionHas('success');
    $this->actingAs($b)->post(route('activities.reviews.store', $activity), ['rating' => 4])
        ->assertSessionHas('success');

    $this->assertDatabaseCount('reviews', 2);
    $this->actingAs($host)->get(route('activities.show', $activity))
        ->assertOk()->assertSee('4.5')->assertSee('จาก 2 รีวิว')->assertSee('สนุกมาก');
    $this->actingAs($host)->get(route('activities.index', ['status' => 'all']))
        ->assertOk()->assertSee('(2 รีวิว)');
});

test('คนที่ไม่ได้เข้าร่วมรีวิวไม่ได้', function () {
    $host = p5User('host@test.com');
    $stranger = p5User('s@test.com');
    $activity = p5Activity($host);

    $this->actingAs($stranger)->post(route('activities.reviews.store', $activity), ['rating' => 5])
        ->assertSessionHas('error');
    $this->assertDatabaseCount('reviews', 0);
});

test('ผู้ได้รับอนุมัติแต่ขาดนัด หรือยังรออนุมัติ รีวิวไม่ได้', function () {
    $host = p5User('host@test.com');
    $absent = p5User('absent@test.com');
    $pending = p5User('pending@test.com');
    $activity = p5Activity($host);
    p5Participant($activity, $absent, 'approved', 'absent');
    p5Participant($activity, $pending, 'pending', null);

    $this->actingAs($absent)->post(route('activities.reviews.store', $activity), ['rating' => 3])->assertSessionHas('error');
    $this->actingAs($pending)->post(route('activities.reviews.store', $activity), ['rating' => 3])->assertSessionHas('error');
    $this->assertDatabaseCount('reviews', 0);
});

test('ผู้จัดรีวิวกิจกรรมของตัวเองไม่ได้', function () {
    $host = p5User('host@test.com');
    $activity = p5Activity($host);

    $this->actingAs($host)->post(route('activities.reviews.store', $activity), ['rating' => 5])
        ->assertSessionHas('error', 'ผู้จัดไม่สามารถรีวิวกิจกรรมของตัวเองได้');
    $this->assertDatabaseCount('reviews', 0);
});

test('รีวิวซ้ำไม่ได้', function () {
    $host = p5User('host@test.com');
    $a = p5User('a@test.com');
    $activity = p5Activity($host);
    p5Participant($activity, $a);

    $this->actingAs($a)->post(route('activities.reviews.store', $activity), ['rating' => 5]);
    $this->actingAs($a)->post(route('activities.reviews.store', $activity), ['rating' => 1])
        ->assertSessionHas('error', 'คุณรีวิวกิจกรรมนี้ไปแล้ว');
    $this->assertDatabaseCount('reviews', 1);
});

test('รีวิวก่อนกิจกรรมจบไม่ได้', function () {
    $host = p5User('host@test.com');
    $a = p5User('a@test.com');
    $activity = p5Activity($host, ended: false);
    p5Participant($activity, $a);

    $this->actingAs($a)->post(route('activities.reviews.store', $activity), ['rating' => 5])->assertSessionHas('error');
    $this->assertDatabaseCount('reviews', 0);
});

test('คะแนนต้องอยู่ระหว่าง 1-5', function () {
    $host = p5User('host@test.com');
    $a = p5User('a@test.com');
    $activity = p5Activity($host);
    p5Participant($activity, $a);

    $this->actingAs($a)->post(route('activities.reviews.store', $activity), ['rating' => 6])->assertSessionHasErrors('rating');
    $this->actingAs($a)->post(route('activities.reviews.store', $activity), ['rating' => 0])->assertSessionHasErrors('rating');
    $this->assertDatabaseCount('reviews', 0);
});

test('หน้ากิจกรรมแสดงฟอร์มรีวิวเฉพาะผู้มีสิทธิ์', function () {
    $host = p5User('host@test.com');
    $a = p5User('a@test.com');
    $stranger = p5User('s@test.com');
    $activity = p5Activity($host);
    p5Participant($activity, $a);

    $this->actingAs($a)->get(route('activities.show', $activity))->assertSee('ส่งรีวิว');
    $this->actingAs($stranger)->get(route('activities.show', $activity))
        ->assertDontSee('ส่งรีวิว')->assertSee('เฉพาะผู้ที่ได้รับการเช็กชื่อว่ามาเข้าร่วมจริงเท่านั้นที่รีวิวได้');
});

// ──────────────── รายงาน ────────────────

test('ผู้ใช้รายงานกิจกรรมและผู้จัดได้ แต่รายงานซ้ำขณะรอตรวจไม่ได้', function () {
    $host = p5User('host@test.com');
    $a = p5User('a@test.com');
    $activity = p5Activity($host, ended: false);

    $this->actingAs($a)->post(route('activities.reports.store', $activity), ['target' => 'activity', 'reason' => 'เนื้อหาไม่เหมาะสม'])
        ->assertSessionHas('success');
    $this->actingAs($a)->post(route('activities.reports.store', $activity), ['target' => 'user:'.$host->id, 'reason' => 'ผู้จัดพูดจาไม่สุภาพ'])
        ->assertSessionHas('success');
    $this->actingAs($a)->post(route('activities.reports.store', $activity), ['target' => 'activity', 'reason' => 'รายงานซ้ำอีกครั้ง'])
        ->assertSessionHas('error');

    $this->assertDatabaseCount('reports', 2);
    $this->assertDatabaseHas('reports', ['reporter_id' => $a->id, 'target_type' => 'activity', 'target_id' => $activity->id, 'status' => 'pending']);
    $this->assertDatabaseHas('reports', ['reporter_id' => $a->id, 'target_type' => 'user', 'target_id' => $host->id]);
});

test('รายงานตัวเอง กิจกรรมของตัวเอง หรือผู้ใช้ที่ไม่เกี่ยวข้องไม่ได้', function () {
    $host = p5User('host@test.com');
    $a = p5User('a@test.com');
    $outsider = p5User('out@test.com');
    $activity = p5Activity($host, ended: false);

    $this->actingAs($host)->post(route('activities.reports.store', $activity), ['target' => 'activity', 'reason' => 'ทดสอบรายงาน'])->assertSessionHas('error');
    $this->actingAs($a)->post(route('activities.reports.store', $activity), ['target' => 'user:'.$a->id, 'reason' => 'ทดสอบรายงาน'])->assertSessionHas('error');
    $this->actingAs($a)->post(route('activities.reports.store', $activity), ['target' => 'user:'.$outsider->id, 'reason' => 'ทดสอบรายงาน'])->assertSessionHas('error');
    $this->actingAs($a)->post(route('activities.reports.store', $activity), ['target' => 'activity', 'reason' => ''])->assertSessionHasErrors('reason');

    $this->assertDatabaseCount('reports', 0);
});

// ──────────────── Admin ตรวจรายงาน ────────────────

test('นักศึกษาเข้าหน้าตรวจรายงานไม่ได้', function () {
    $a = p5User('a@test.com');
    $this->actingAs($a)->get(route('admin.reports.index'))->assertForbidden();
});

test('Admin ซ่อนกิจกรรมพร้อมเหตุผล: บันทึกประวัติ แจ้งผู้รายงาน และคนอื่นมองไม่เห็นกิจกรรม', function () {
    $admin = p5User('admin@test.com', 'admin');
    $host = p5User('host@test.com');
    $a = p5User('a@test.com');
    $activity = p5Activity($host, ended: false);
    $this->actingAs($a)->post(route('activities.reports.store', $activity), ['target' => 'activity', 'reason' => 'โพสต์ขายของ ไม่ใช่กิจกรรม']);
    $report = Report::firstOrFail();

    $this->actingAs($admin)->get(route('admin.reports.index'))->assertOk()->assertSee('โพสต์ขายของ ไม่ใช่กิจกรรม');

    $this->actingAs($admin)->patch(route('admin.reports.resolve', $report), ['action' => 'hide', 'note' => 'ผิดกฎการโพสต์'])
        ->assertSessionHas('success');

    expect($activity->fresh()->hidden_at)->not->toBeNull();
    expect($report->fresh()->status)->toBe('actioned');
    expect($report->fresh()->handled_by)->toBe($admin->id);
    $this->assertDatabaseHas('moderation_logs', ['admin_id' => $admin->id, 'action' => 'hide_activity', 'target_id' => $activity->id, 'reason' => 'ผิดกฎการโพสต์']);
    expect($a->fresh()->notifications)->toHaveCount(1);

    // คนอื่นมองไม่เห็น / ขอเข้าร่วมไม่ได้ แต่เจ้าของยังเห็นพร้อมเหตุผล
    $this->actingAs($a)->get(route('activities.index'))->assertDontSee('เล่นบาสเย็นนี้');
    $this->actingAs($a)->get(route('activities.show', $activity))->assertNotFound();
    $this->actingAs($a)->post(route('activities.join', $activity))->assertForbidden();
    $this->actingAs($host)->get(route('activities.show', $activity))->assertOk()->assertSee('ผิดกฎการโพสต์');

    // หน้า Admin แสดงประวัติการดำเนินการ
    $this->actingAs($admin)->get(route('admin.reports.index', ['status' => 'all']))
        ->assertOk()->assertSee('ซ่อนกิจกรรม')->assertSee('ผิดกฎการโพสต์');
});

test('Admin ระงับผู้ใช้ที่ถูกรายงาน และผู้ใช้นั้นใช้งานต่อไม่ได้', function () {
    $admin = p5User('admin@test.com', 'admin');
    $host = p5User('host@test.com');
    $a = p5User('a@test.com');
    $activity = p5Activity($host, ended: false);
    $this->actingAs($a)->post(route('activities.reports.store', $activity), ['target' => 'user:'.$host->id, 'reason' => 'หลอกให้โอนเงิน']);
    $report = Report::firstOrFail();

    $this->actingAs($admin)->patch(route('admin.reports.resolve', $report), ['action' => 'hide', 'note' => 'ยืนยันว่าหลอกลวง']);

    expect($host->fresh()->status)->toBe('suspended');
    $this->assertDatabaseHas('moderation_logs', ['action' => 'suspend_user', 'target_id' => $host->id]);
    $this->actingAs($host->fresh())->get(route('activities.index'))->assertRedirect(route('login'));
});

test('Admin ยกรายงานได้ ต้องใส่เหตุผล และดำเนินการซ้ำไม่ได้', function () {
    $admin = p5User('admin@test.com', 'admin');
    $host = p5User('host@test.com');
    $a = p5User('a@test.com');
    $activity = p5Activity($host, ended: false);
    $this->actingAs($a)->post(route('activities.reports.store', $activity), ['target' => 'activity', 'reason' => 'ไม่ชอบกิจกรรมนี้']);
    $report = Report::firstOrFail();

    $this->actingAs($admin)->patch(route('admin.reports.resolve', $report), ['action' => 'dismiss', 'note' => ''])
        ->assertSessionHasErrors('note');
    expect($report->fresh()->status)->toBe('pending');

    $this->actingAs($admin)->patch(route('admin.reports.resolve', $report), ['action' => 'dismiss', 'note' => 'ไม่ผิดกฎ']);
    expect($report->fresh()->status)->toBe('dismissed');
    expect($activity->fresh()->hidden_at)->toBeNull();

    $this->actingAs($admin)->patch(route('admin.reports.resolve', $report), ['action' => 'hide', 'note' => 'เปลี่ยนใจ'])
        ->assertSessionHas('error');
    expect($activity->fresh()->hidden_at)->toBeNull();
    expect(ModerationLog::count())->toBe(1);
});

test('Admin ระงับบัญชี Admin ด้วยกันผ่านรายงานไม่ได้', function () {
    $admin = p5User('admin@test.com', 'admin');
    $admin2 = p5User('admin2@test.com', 'admin');
    $a = p5User('a@test.com');
    $activity = p5Activity($admin2, ended: false);
    $this->actingAs($a)->post(route('activities.reports.store', $activity), ['target' => 'user:'.$admin2->id, 'reason' => 'ทดสอบรายงาน admin']);

    $this->actingAs($admin)->patch(route('admin.reports.resolve', Report::firstOrFail()), ['action' => 'hide', 'note' => 'ทดสอบ'])
        ->assertSessionHas('error');
    expect($admin2->fresh()->status)->toBe('active');
});

test('Admin เลิกซ่อนกิจกรรมพร้อมบันทึกเหตุผล', function () {
    $admin = p5User('admin@test.com', 'admin');
    $host = p5User('host@test.com');
    $activity = p5Activity($host, ended: false);
    $activity->hidden_at = now();
    $activity->hidden_reason = 'ทดสอบ';
    $activity->save();

    $this->actingAs($admin)->patch(route('admin.activities.unhide', $activity), ['note' => 'ตรวจแล้วไม่ผิด'])
        ->assertSessionHas('success');

    expect($activity->fresh()->hidden_at)->toBeNull();
    $this->assertDatabaseHas('moderation_logs', ['action' => 'unhide_activity', 'target_id' => $activity->id, 'reason' => 'ตรวจแล้วไม่ผิด']);
});

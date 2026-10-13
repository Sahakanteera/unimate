<?php

use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\Category;
use App\Models\ModerationLog;
use App\Models\Report;
use App\Models\Review;
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

// ──────────────── แก้ไข/ลบรีวิว และการแจ้งเตือน ────────────────

test('ผู้จัดได้รับแจ้งเตือนเมื่อมีรีวิวใหม่', function () {
    $host = p5User('host@test.com');
    $a = p5User('a@test.com');
    $activity = p5Activity($host);
    p5Participant($activity, $a);

    $this->actingAs($a)->post(route('activities.reviews.store', $activity), ['rating' => 4]);

    expect($host->fresh()->notifications)->toHaveCount(1);
    expect($host->fresh()->notifications->first()->data['message'])->toContain('4 ดาว')->toContain('เล่นบาสเย็นนี้');
});

test('เจ้าของรีวิวแก้ไขและลบรีวิวของตัวเองได้ และหน้ากิจกรรมแสดงฟอร์มแก้ไข', function () {
    $host = p5User('host@test.com');
    $a = p5User('a@test.com');
    $activity = p5Activity($host);
    p5Participant($activity, $a);
    $this->actingAs($a)->post(route('activities.reviews.store', $activity), ['rating' => 2, 'comment' => 'ธรรมดา']);
    $review = Review::firstOrFail();

    $this->actingAs($a)->get(route('activities.show', $activity))
        ->assertOk()->assertSee('รีวิวของคุณ')->assertSee('บันทึกการแก้ไข')
        ->assertDontSee('ส่งรีวิว')->assertDontSee('คุณรีวิวกิจกรรมนี้ไปแล้ว');

    $this->actingAs($a)->patch(route('activities.reviews.update', [$activity, $review]), ['rating' => 5, 'comment' => 'ดีกว่าที่คิด'])
        ->assertSessionHas('success');
    expect($review->fresh()->rating)->toBe(5);
    expect($review->fresh()->comment)->toBe('ดีกว่าที่คิด');

    $this->actingAs($a)->patch(route('activities.reviews.update', [$activity, $review]), ['rating' => 9])
        ->assertSessionHasErrors('rating');
    expect($review->fresh()->rating)->toBe(5);

    $this->actingAs($a)->delete(route('activities.reviews.destroy', [$activity, $review]))->assertSessionHas('success');
    $this->assertDatabaseCount('reviews', 0);
});

test('แก้ไขหรือลบรีวิวของคนอื่นไม่ได้ และรีวิวต้องเป็นของกิจกรรมใน URL', function () {
    $host = p5User('host@test.com');
    $a = p5User('a@test.com');
    $b = p5User('b@test.com');
    $activity = p5Activity($host);
    $other = p5Activity($host);
    p5Participant($activity, $a);
    $this->actingAs($a)->post(route('activities.reviews.store', $activity), ['rating' => 4]);
    $review = Review::firstOrFail();

    $this->actingAs($b)->patch(route('activities.reviews.update', [$activity, $review]), ['rating' => 1])->assertForbidden();
    $this->actingAs($b)->delete(route('activities.reviews.destroy', [$activity, $review]))->assertForbidden();
    $this->actingAs($host)->delete(route('activities.reviews.destroy', [$activity, $review]))->assertForbidden();
    $this->actingAs($a)->patch(route('activities.reviews.update', [$other, $review]), ['rating' => 1])->assertNotFound();

    expect($review->fresh()->rating)->toBe(4);
});

test('แก้ไขรีวิวในกิจกรรมที่ถูกซ่อนไม่ได้', function () {
    $host = p5User('host@test.com');
    $a = p5User('a@test.com');
    $activity = p5Activity($host);
    p5Participant($activity, $a);
    $this->actingAs($a)->post(route('activities.reviews.store', $activity), ['rating' => 4]);
    $activity->hidden_at = now();
    $activity->save();

    $this->actingAs($a)->patch(route('activities.reviews.update', [$activity, Review::firstOrFail()]), ['rating' => 1])
        ->assertSessionHas('error');
    expect(Review::firstOrFail()->rating)->toBe(4);
});

test('Admin ลบรีวิวไม่เหมาะสมพร้อมเหตุผล บันทึกประวัติ และแจ้งผู้เขียนรีวิว', function () {
    $admin = p5User('admin@test.com', 'admin');
    $host = p5User('host@test.com');
    $a = p5User('a@test.com');
    $activity = p5Activity($host);
    p5Participant($activity, $a);
    $this->actingAs($a)->post(route('activities.reviews.store', $activity), ['rating' => 1, 'comment' => 'คำหยาบ']);
    $review = Review::firstOrFail();

    $this->actingAs($admin)->get(route('activities.show', $activity))->assertOk()->assertSee('ลบรีวิวนี้ (ผู้ดูแลระบบ)');

    $this->actingAs($admin)->delete(route('admin.reviews.destroy', $review), ['moderation_reason' => ''])
        ->assertSessionHasErrors('moderation_reason');
    $this->assertDatabaseCount('reviews', 1);

    $this->actingAs($admin)->delete(route('admin.reviews.destroy', $review), ['moderation_reason' => 'ใช้คำไม่สุภาพ'])
        ->assertSessionHas('success');

    $this->assertDatabaseCount('reviews', 0);
    $this->assertDatabaseHas('moderation_logs', [
        'admin_id' => $admin->id, 'action' => 'delete_review', 'target_type' => 'review', 'target_id' => $review->id, 'reason' => 'ใช้คำไม่สุภาพ',
    ]);
    expect($a->fresh()->notifications->last()->data['message'])->toContain('ใช้คำไม่สุภาพ');

    $this->actingAs($admin)->get(route('admin.reports.index', ['status' => 'all']))->assertOk()->assertSee('ลบรีวิว');
});

test('นักศึกษาใช้ช่องทาง Admin ลบรีวิวคนอื่นไม่ได้ และไม่เห็นปุ่มลบของผู้ดูแล', function () {
    $host = p5User('host@test.com');
    $a = p5User('a@test.com');
    $b = p5User('b@test.com');
    $activity = p5Activity($host);
    p5Participant($activity, $a);
    p5Participant($activity, $b);
    $this->actingAs($a)->post(route('activities.reviews.store', $activity), ['rating' => 5]);

    $this->actingAs($b)->get(route('activities.show', $activity))->assertOk()->assertDontSee('ลบรีวิวนี้ (ผู้ดูแลระบบ)');
    $this->actingAs($b)->delete(route('admin.reviews.destroy', Review::firstOrFail()), ['moderation_reason' => 'ไม่ชอบรีวิวนี้'])
        ->assertForbidden();
    $this->assertDatabaseCount('reviews', 1);
    expect(ModerationLog::count())->toBe(0);
});

test('นัดของฉันมีปุ่มให้คะแนนเฉพาะกิจกรรมที่จบแล้ว มาจริง และยังไม่รีวิว', function () {
    $host = p5User('host@test.com');
    $a = p5User('a@test.com');
    $activity = p5Activity($host);
    p5Participant($activity, $a);

    $this->actingAs($a)->get(route('my-activities.index'))->assertOk()
        ->assertSee('ให้คะแนน')->assertSee(route('activities.show', $activity).'#reviews', false);

    $this->actingAs($a)->post(route('activities.reviews.store', $activity), ['rating' => 5]);
    // ตรวจจากลิงก์ เพราะคำว่า "ให้คะแนน" มีในคอมเมนต์ CSS ของธีมด้วย
    $this->actingAs($a)->get(route('my-activities.index'))->assertOk()
        ->assertDontSee(route('activities.show', $activity).'#reviews', false);
});

// ──────────────── รายงานรีวิว ────────────────

/** สร้างกิจกรรมที่จบแล้ว พร้อมรีวิว 1 ดาวจาก $author */
function p5ReviewedActivity(User $host, User $author, string $comment = 'รีวิวคำหยาบ'): Review
{
    $activity = p5Activity($host);
    p5Participant($activity, $author);
    $review = new Review(['rating' => 1, 'comment' => $comment]);
    $review->activity()->associate($activity);
    $review->user()->associate($author);
    $review->save();

    return $review;
}

test('ผู้ใช้รายงานรีวิวของคนอื่นได้ แต่รายงานรีวิวตัวเองหรือรายงานซ้ำขณะรอตรวจไม่ได้', function () {
    $host = p5User('host@test.com');
    $a = p5User('a@test.com');
    $b = p5User('b@test.com');
    $review = p5ReviewedActivity($host, $a);
    $activity = $review->activity;
    $url = route('activities.reviews.report', [$activity, $review]);

    // ปุ่มรายงานแสดงกับคนอื่น แต่ไม่แสดงกับเจ้าของรีวิว
    $this->actingAs($b)->get(route('activities.show', $activity))->assertOk()->assertSee($url, false);
    $this->actingAs($a)->get(route('activities.show', $activity))->assertOk()->assertDontSee($url, false);

    $this->actingAs($b)->post($url, ['review_reason' => ''])
        ->assertSessionHasErrors('review_reason')->assertSessionDoesntHaveErrors('reason');
    $this->actingAs($b)->post($url, ['review_reason' => 'ใช้คำหยาบใส่ผู้จัด'])->assertSessionHas('success');
    $this->actingAs($b)->post($url, ['review_reason' => 'รายงานซ้ำอีกครั้ง'])
        ->assertSessionHas('error', 'คุณรายงานเรื่องนี้ไปแล้ว กำลังรอผู้ดูแลระบบตรวจสอบ');
    $this->actingAs($a)->post($url, ['review_reason' => 'รายงานรีวิวตัวเอง'])->assertSessionHas('error');

    $this->assertDatabaseCount('reports', 1);
    $this->assertDatabaseHas('reports', ['reporter_id' => $b->id, 'target_type' => 'review', 'target_id' => $review->id, 'status' => 'pending']);
});

test('รายงานรีวิวผ่าน URL ของกิจกรรมอื่นได้ 404 และ Admin ไม่เห็นปุ่มรายงานรีวิว', function () {
    $admin = p5User('admin@test.com', 'admin');
    $host = p5User('host@test.com');
    $a = p5User('a@test.com');
    $b = p5User('b@test.com');
    $review = p5ReviewedActivity($host, $a);
    $other = p5Activity($host);

    $this->actingAs($b)->post(route('activities.reviews.report', [$other, $review]), ['review_reason' => 'ผิดกิจกรรม'])->assertNotFound();
    $this->assertDatabaseCount('reports', 0);

    $this->actingAs($admin)->get(route('activities.show', $review->activity))->assertOk()
        ->assertDontSee(route('activities.reviews.report', [$review->activity, $review]), false);
});

test('Admin ลบรีวิวผ่านรายงาน: รีวิวหายไป บันทึกประวัติพร้อม report_id ปิดรายงาน และแจ้งทั้งผู้เขียนและผู้รายงาน', function () {
    $admin = p5User('admin@test.com', 'admin');
    $host = p5User('host@test.com');
    $a = p5User('a@test.com');
    $b = p5User('b@test.com');
    $review = p5ReviewedActivity($host, $a, 'ความเห็นไม่สุภาพมาก');
    $this->actingAs($b)->post(route('activities.reviews.report', [$review->activity, $review]), ['review_reason' => 'ใช้คำไม่สุภาพ']);
    $report = Report::firstOrFail();

    $this->actingAs($admin)->get(route('admin.reports.index'))->assertOk()
        ->assertSee('ความเห็นไม่สุภาพมาก')->assertSee('ใช้คำไม่สุภาพ')
        ->assertSee(route('users.show', $a), false)
        ->assertSee(route('activities.show', $review->activity).'#reviews', false);

    $this->actingAs($admin)->patch(route('admin.reports.resolve', $report), ['action' => 'hide', 'note' => 'ผิดกฎชุมชน'])
        ->assertSessionHas('success');

    $this->assertDatabaseCount('reviews', 0);
    $this->assertDatabaseHas('moderation_logs', [
        'admin_id' => $admin->id, 'report_id' => $report->id, 'action' => 'delete_review',
        'target_type' => 'review', 'target_id' => $review->id, 'reason' => 'ผิดกฎชุมชน',
    ]);
    expect(ModerationLog::count())->toBe(1);
    expect($report->fresh()->status)->toBe('actioned');
    expect($report->fresh()->handled_by)->toBe($admin->id);
    expect($a->fresh()->notifications->last()->data['message'])->toContain('ผิดกฎชุมชน');
    expect($b->fresh()->notifications->last()->data['message'])->toContain('ดำเนินการกับรายงานของคุณแล้ว');

    // หน้า Admin ยังเปิดได้ และแสดงว่ารีวิวถูกลบแล้ว
    $this->actingAs($admin)->get(route('admin.reports.index', ['status' => 'all']))->assertOk()->assertSee('รีวิวถูกลบแล้ว');
});

test('Admin ยกรายงานรีวิวได้ รีวิวยังอยู่', function () {
    $admin = p5User('admin@test.com', 'admin');
    $host = p5User('host@test.com');
    $a = p5User('a@test.com');
    $b = p5User('b@test.com');
    $review = p5ReviewedActivity($host, $a);
    $this->actingAs($b)->post(route('activities.reviews.report', [$review->activity, $review]), ['review_reason' => 'ไม่ชอบรีวิวนี้']);
    $report = Report::firstOrFail();

    $this->actingAs($admin)->patch(route('admin.reports.resolve', $report), ['action' => 'dismiss', 'note' => 'ไม่ผิดกฎ'])
        ->assertSessionHas('success');

    $this->assertDatabaseCount('reviews', 1);
    expect($report->fresh()->status)->toBe('dismissed');
    $this->assertDatabaseHas('moderation_logs', ['action' => 'dismiss', 'target_type' => 'review', 'report_id' => $report->id]);
    expect($b->fresh()->notifications)->toHaveCount(1);
    expect($a->fresh()->notifications)->toHaveCount(0);
});

test('Admin ลบรีวิวจากหน้ากิจกรรม: รายงานของรีวิวนั้นที่รอตรวจถูกปิด และหน้าตรวจรายงานไม่พัง', function () {
    $admin = p5User('admin@test.com', 'admin');
    $host = p5User('host@test.com');
    $a = p5User('a@test.com');
    $b = p5User('b@test.com');
    $review = p5ReviewedActivity($host, $a);
    $this->actingAs($b)->post(route('activities.reviews.report', [$review->activity, $review]), ['review_reason' => 'ใช้คำไม่สุภาพ']);
    $this->actingAs($host)->post(route('activities.reviews.report', [$review->activity, $review]), ['review_reason' => 'ข้อมูลเท็จทั้งหมด']);
    expect(Report::where('status', 'pending')->count())->toBe(2);

    $this->actingAs($admin)->delete(route('admin.reviews.destroy', $review), ['moderation_reason' => 'ลบตามรายงาน'])
        ->assertSessionHas('success');

    expect(Report::where('status', 'actioned')->where('handled_by', $admin->id)->count())->toBe(2);
    expect($b->fresh()->notifications)->toHaveCount(1);
    $this->assertDatabaseHas('moderation_logs', ['action' => 'delete_review', 'report_id' => null, 'target_id' => $review->id]);

    $this->actingAs($admin)->get(route('admin.reports.index', ['status' => 'all']))->assertOk()->assertSee('รีวิวถูกลบแล้ว');
});

test('รายงานรีวิวที่ถูกลบไปก่อนแล้ว: หน้า Admin แสดงว่าถูกลบ ลบซ้ำไม่ได้ แต่ยกรายงานได้', function () {
    $admin = p5User('admin@test.com', 'admin');
    $host = p5User('host@test.com');
    $a = p5User('a@test.com');
    $b = p5User('b@test.com');
    $review = p5ReviewedActivity($host, $a);
    $this->actingAs($b)->post(route('activities.reviews.report', [$review->activity, $review]), ['review_reason' => 'ใช้คำไม่สุภาพ']);
    $report = Report::firstOrFail();
    $review->delete(); // เจ้าของลบเอง รายงานยังรอตรวจ

    $this->actingAs($admin)->get(route('admin.reports.index'))->assertOk()
        ->assertSee('รีวิวถูกลบแล้ว')->assertDontSee('value="hide"', false);

    $this->actingAs($admin)->patch(route('admin.reports.resolve', $report), ['action' => 'hide', 'note' => 'ลบรีวิว'])
        ->assertSessionHas('error');
    $this->actingAs($admin)->patch(route('admin.reports.resolve', $report), ['action' => 'dismiss', 'note' => 'รีวิวถูกลบไปแล้ว'])
        ->assertSessionHas('success');
    expect($report->fresh()->status)->toBe('dismissed');
});

// ──────────────── รอบตรวจงาน: กรณีขอบที่เคยหลุด ────────────────

test('กิจกรรมที่ถูกยกเลิกหรือถูกซ่อน รีวิวผ่าน URL ตรงไม่ได้', function () {
    $host = p5User('edge-host@test.com');
    $member = p5User('edge-member@test.com');

    $cancelled = p5Activity($host);
    $cancelled->status = 'cancelled';
    $cancelled->save();
    p5Participant($cancelled, $member);
    $this->actingAs($member)->post(route('activities.reviews.store', $cancelled), ['rating' => 5])->assertSessionHas('error');

    $hidden = p5Activity($host);
    $hidden->hidden_at = now();
    $hidden->save();
    p5Participant($hidden, $member);
    $this->post(route('activities.reviews.store', $hidden), ['rating' => 5])->assertSessionHas('error');

    expect(Review::count())->toBe(0);
});

test('รายงานกิจกรรมที่ถูกซ่อนผ่าน URL ตรงไม่ได้ (เห็นเป็น 404 เหมือนหน้ากิจกรรม)', function () {
    $host = p5User('hide-host@test.com');
    $other = p5User('hide-other@test.com');
    $activity = p5Activity($host);
    $activity->hidden_at = now();
    $activity->save();

    $this->actingAs($other)->post(route('activities.reports.store', $activity), ['target' => 'activity', 'reason' => 'ลองรายงานดู'])->assertNotFound();
    expect(Report::count())->toBe(0);
});

test('Admin ไม่เห็นฟอร์มรายงานและข้อความสิทธิ์รีวิวในหน้ากิจกรรม', function () {
    $host = p5User('adm-host@test.com');
    $admin = p5User('adm@test.com', 'admin');
    $activity = p5Activity($host);

    $this->actingAs($admin)->get(route('activities.show', $activity))->assertOk()
        ->assertDontSee(route('activities.reports.store', $activity))
        ->assertDontSee('เฉพาะผู้ที่ได้รับการเช็กชื่อว่ามาเข้าร่วมจริงเท่านั้นที่รีวิวได้');
});

test('หน้าจัดการคำขอ: กิจกรรมเริ่มแล้วไม่มีปุ่มอนุมัติ และชื่อที่มีเครื่องหมายคำพูดไม่ทำให้สคริปต์ยืนยันพัง', function () {
    $host = p5User('req-host@test.com');
    $guest = p5User('req-guest@test.com');
    $guest->name = "O'Neil </script>";
    $guest->save();

    $started = p5Activity($host, false);
    $started->starts_at = now()->subHour();
    $started->ends_at = now()->addHour();
    $started->save();
    $p = p5Participant($started, $guest, 'pending', null);

    $html = $this->actingAs($host)->get(route('activities.requests', $started))->assertOk()
        ->assertDontSee(route('activities.requests.approve', [$started, $p]))
        ->assertSee('ปิดรับแล้ว')
        ->getContent();

    // ชื่อผู้ใช้ต้องไม่หลุดออกจากสตริง JavaScript ใน onsubmit
    expect($html)->not->toContain("O'Neil")->not->toContain('</script>?');
});

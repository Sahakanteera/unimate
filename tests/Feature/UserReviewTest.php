<?php

use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\Category;
use App\Models\ModerationLog;
use App\Models\Report;
use App\Models\User;
use App\Models\UserReview;
use Illuminate\Support\Facades\DB;

// ──────────────── Helpers (ชื่อขึ้นต้น ur เพื่อไม่ชนกับ helper ในไฟล์ทดสอบอื่น) ────────────────

function urUser(string $email, string $role = 'student'): User
{
    return User::create([
        'name' => 'ผู้ใช้ '.strtok($email, '@'),
        'email' => $email,
        'password' => 'password',
        'role' => $role,
        'status' => 'active',
    ]);
}

/** สร้างกิจกรรมโดยตรง (ข้าม validation เวลาในอนาคต) เพื่อทดสอบกิจกรรมที่จบแล้ว */
function urActivity(User $host, string $title = 'ปิงปองหลังเลิกเรียน', bool $ended = true): Activity
{
    $activity = new Activity([
        'category_id' => Category::firstOrCreate(['name' => 'กีฬา'])->id,
        'title' => $title,
        'description' => 'ทดสอบรีวิวคน',
        'location' => 'โรงยิม',
        'starts_at' => $ended ? now()->subDays(2) : now()->addDays(2),
        'ends_at' => $ended ? now()->subDays(2)->addHours(2) : now()->addDays(2)->addHours(2),
        'capacity' => 10,
    ]);
    $activity->user()->associate($host);
    $activity->status = 'published';
    $activity->save();

    return $activity;
}

function urJoin(Activity $activity, User $user, string $status = 'approved', ?string $attendance = 'present'): ActivityParticipant
{
    $p = new ActivityParticipant;
    $p->activity()->associate($activity);
    $p->user()->associate($user);
    $p->status = $status;
    $p->attendance = $attendance;
    $p->save();

    return $p;
}

/** @param 'host'|'participant' $role */
function urReview(Activity $activity, User $reviewer, User $reviewee, string $role, int $rating, ?string $comment = null): UserReview
{
    $review = new UserReview(['rating' => $rating, 'comment' => $comment]);
    $review->activity()->associate($activity);
    $review->reviewer()->associate($reviewer);
    $review->reviewee()->associate($reviewee);
    $review->reviewee_role = $role;
    $review->save();

    return $review;
}

/**
 * กิจกรรมที่จบแล้ว: host + a, b มาจริง, absent ขาด, pending รออนุมัติ
 *
 * @return array{0: Activity, 1: User, 2: User, 3: User, 4: User, 5: User}
 */
function urScenario(): array
{
    $host = urUser('ur-host@test.com');
    $a = urUser('ur-a@test.com');
    $b = urUser('ur-b@test.com');
    $absent = urUser('ur-absent@test.com');
    $pending = urUser('ur-pending@test.com');
    $activity = urActivity($host);
    urJoin($activity, $a);
    urJoin($activity, $b);
    urJoin($activity, $absent, 'approved', 'absent');
    urJoin($activity, $pending, 'pending', null);

    return [$activity, $host, $a, $b, $absent, $pending];
}

// ──────────────── สร้างรีวิว ────────────────

test('คำขอรีวิวคนที่ชนกันไม่ทำให้หน้าเว็บล้มและไม่เขียนทับหรือแจ้งเตือนซ้ำ', function () {
    [$activity, $host, $a] = urScenario();
    $inserted = false;

    // จำลองอีกคำขอบันทึกคู่เดียวกัน หลังผ่านการตรวจซ้ำแต่ก่อนคำขอนี้บันทึก
    UserReview::creating(function (UserReview $review) use (&$inserted) {
        if (! $inserted) {
            $inserted = true;
            DB::table('user_reviews')->insert([
                'activity_id' => $review->activity_id,
                'reviewer_id' => $review->reviewer_id,
                'reviewee_id' => $review->reviewee_id,
                'reviewee_role' => $review->reviewee_role,
                'rating' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    });

    $this->withoutExceptionHandling()->actingAs($a)
        ->from(route('activities.people', $activity))
        ->post(route('activities.people.review', [$activity, $host]), ['rating' => 5])
        ->assertRedirect(route('activities.people', $activity))
        ->assertSessionHas('error', 'คุณรีวิวผู้ใช้นี้ในกิจกรรมนี้ไปแล้ว');

    $this->assertDatabaseCount('user_reviews', 1);
    $this->assertDatabaseHas('user_reviews', ['reviewer_id' => $a->id, 'reviewee_id' => $host->id, 'rating' => 2]);
    expect($host->fresh()->notifications)->toHaveCount(0);
});

test('ผู้เข้าร่วมรีวิวผู้จัดและเพื่อน ผู้จัดรีวิวผู้เข้าร่วมได้ พร้อมบันทึกบทบาทและแจ้งเตือนผู้ถูกรีวิว', function () {
    [$activity, $host, $a, $b] = urScenario();

    $this->actingAs($a)->post(route('activities.people.review', [$activity, $host]), ['rating' => 5, 'comment' => 'ผู้จัดใจดี'])
        ->assertSessionHas('success');
    $this->actingAs($a)->post(route('activities.people.review', [$activity, $b]), ['rating' => 4])
        ->assertSessionHas('success');
    $this->actingAs($host)->post(route('activities.people.review', [$activity, $a]), ['rating' => 3])
        ->assertSessionHas('success');

    $this->assertDatabaseCount('user_reviews', 3);
    $this->assertDatabaseHas('user_reviews', ['reviewer_id' => $a->id, 'reviewee_id' => $host->id, 'reviewee_role' => 'host', 'rating' => 5]);
    $this->assertDatabaseHas('user_reviews', ['reviewer_id' => $a->id, 'reviewee_id' => $b->id, 'reviewee_role' => 'participant']);
    $this->assertDatabaseHas('user_reviews', ['reviewer_id' => $host->id, 'reviewee_id' => $a->id, 'reviewee_role' => 'participant']);

    // แจ้งเตือนแบบ relative URL ไปโปรไฟล์ของผู้ถูกรีวิว (แท็บตามบทบาท)
    $data = $host->fresh()->notifications->first()->data;
    expect($data['kind'])->toBe('review');
    expect($data['message'])->toBe("{$a->name} ให้คะแนนคุณ 5 ดาว จากกิจกรรม '{$activity->title}'");
    expect($data['url'])->toBe(route('users.show', ['user' => $host, 'tab' => 'host'], false).'#received-reviews');
    expect($data['url'])->toStartWith('/users/');
});

test('รีวิวตัวเอง คนที่ขาด คนที่ยังไม่อนุมัติ หรือคนนอกไม่ได้ และคนที่ขาดรีวิวคนอื่นไม่ได้', function () {
    [$activity, $host, $a, , $absent, $pending] = urScenario();
    $stranger = urUser('ur-stranger@test.com');

    $this->actingAs($a)->post(route('activities.people.review', [$activity, $a]), ['rating' => 5])
        ->assertSessionHas('error', 'รีวิวตัวเองไม่ได้');
    $this->actingAs($a)->post(route('activities.people.review', [$activity, $absent]), ['rating' => 1])
        ->assertSessionHas('error', 'ผู้ใช้นี้ไม่ได้มาร่วมกิจกรรมนี้ จึงรีวิวไม่ได้');
    $this->actingAs($host)->post(route('activities.people.review', [$activity, $pending]), ['rating' => 1])
        ->assertSessionHas('error');
    $this->actingAs($host)->post(route('activities.people.review', [$activity, $stranger]), ['rating' => 1])
        ->assertSessionHas('error');
    $this->actingAs($absent)->post(route('activities.people.review', [$activity, $host]), ['rating' => 1])
        ->assertSessionHas('error');
    $this->actingAs($stranger)->post(route('activities.people.review', [$activity, $host]), ['rating' => 1])
        ->assertSessionHas('error');

    $this->assertDatabaseCount('user_reviews', 0);
});

test('รีวิวซ้ำ รีวิวก่อนจบ กิจกรรมที่ยกเลิก หรือถูกซ่อนไม่ได้ และคะแนนต้องอยู่ระหว่าง 1-5', function () {
    [$activity, $host, $a] = urScenario();

    $this->actingAs($a)->post(route('activities.people.review', [$activity, $host]), ['rating' => 9])
        ->assertSessionHasErrors('rating');
    $this->actingAs($a)->post(route('activities.people.review', [$activity, $host]), ['rating' => 4]);
    $this->actingAs($a)->post(route('activities.people.review', [$activity, $host]), ['rating' => 5])
        ->assertSessionHas('error', 'คุณรีวิวผู้ใช้นี้ในกิจกรรมนี้ไปแล้ว');
    $this->assertDatabaseCount('user_reviews', 1);

    $future = urActivity($host, 'ยังไม่ถึงวัน', ended: false);
    urJoin($future, $a);
    $this->actingAs($a)->post(route('activities.people.review', [$future, $host]), ['rating' => 5])
        ->assertSessionHas('error', 'รีวิวได้หลังกิจกรรมจบแล้วเท่านั้น');

    $cancelled = urActivity($host, 'ถูกยกเลิก');
    urJoin($cancelled, $a);
    $cancelled->status = 'cancelled';
    $cancelled->save();
    $this->actingAs($a)->post(route('activities.people.review', [$cancelled, $host]), ['rating' => 5])
        ->assertSessionHas('error', 'กิจกรรมนี้ถูกยกเลิก จึงรีวิวไม่ได้');

    $hidden = urActivity($host, 'ถูกซ่อน');
    urJoin($hidden, $a);
    $hidden->hidden_at = now();
    $hidden->save();
    $this->actingAs($a)->post(route('activities.people.review', [$hidden, $host]), ['rating' => 5])
        ->assertSessionHas('error', 'กิจกรรมนี้ถูกซ่อนโดยผู้ดูแลระบบ จึงรีวิวไม่ได้');

    $this->assertDatabaseCount('user_reviews', 1);
});

// ──────────────── หน้า people ────────────────

test('หน้ารีวิวเพื่อนร่วมกิจกรรมเปิดได้เฉพาะคนที่ไปจริงและ Admin', function () {
    [$activity, $host, $a, $b, $absent, $pending] = urScenario();
    $stranger = urUser('ur-stranger@test.com');
    $admin = urUser('ur-admin@test.com', 'admin');

    // ผู้เข้าร่วมเห็นทุกคนในกลุ่มยกเว้นตัวเอง พร้อมป้ายบทบาทและฟอร์ม
    $response = $this->actingAs($a)->get(route('activities.people', $activity))->assertOk()
        ->assertSee($host->name)->assertSee($b->name)
        ->assertSee('ส่งรีวิวผู้ใช้')
        ->assertSee(route('activities.people.review', [$activity, $host]), false)
        ->assertSee(route('activities.people.review', [$activity, $b]), false)
        ->assertDontSee(route('activities.people.review', [$activity, $a]), false)
        ->assertDontSee(route('activities.people.review', [$activity, $absent]), false);
    expect($response->viewData('people')->pluck('id')->all())->toBe([$host->id, $b->id]);

    $this->actingAs($host)->get(route('activities.people', $activity))->assertOk()
        ->assertSee(route('activities.people.review', [$activity, $a]), false);

    $this->actingAs($absent)->get(route('activities.people', $activity))->assertForbidden();
    $this->actingAs($pending)->get(route('activities.people', $activity))->assertForbidden();
    $this->actingAs($stranger)->get(route('activities.people', $activity))->assertForbidden();

    // Admin ดูอย่างเดียว ไม่มีฟอร์มรีวิว
    $this->actingAs($admin)->get(route('activities.people', $activity))->assertOk()
        ->assertSee($host->name)->assertSee($a->name)
        ->assertDontSee(route('activities.people.review', [$activity, $host]), false);
    $this->actingAs($admin)->post(route('activities.people.review', [$activity, $host]), ['rating' => 5])
        ->assertSessionHas('error');
});

test('กิจกรรมที่ถูกซ่อน ผู้เข้าร่วมเปิดหน้ารีวิวเพื่อนไม่ได้ (404)', function () {
    [$activity, $host, $a] = urScenario();
    $activity->hidden_at = now();
    $activity->save();

    $this->actingAs($a)->get(route('activities.people', $activity))->assertNotFound();
    $this->actingAs($host)->get(route('activities.people', $activity))->assertOk()
        ->assertSee('กิจกรรมนี้ถูกซ่อนโดยผู้ดูแลระบบ จึงรีวิวไม่ได้');
});

test('error ของฟอร์มรีวิวเปิดเฉพาะฟอร์มของคนนั้น', function () {
    [$activity, $host, $a, $b] = urScenario();
    $url = route('activities.people', $activity);

    $this->actingAs($a)->from($url)
        ->post(route('activities.people.review', [$activity, $b]), ['rating' => '', 'comment' => 'ข้อความค้างของบี', 'review_person' => $b->id])
        ->assertRedirect($url)->assertSessionHasErrors('rating');

    // ในเทสต์ (session แบบ array + serialization json) ViewErrorBag ข้ามคำขอไม่ได้ ค่าเดิม (_old_input) ยังอยู่
    // จึงใส่ error ในรูปแบบที่ Session Store เก็บจริงก่อนเปิดหน้า
    $html = $this->actingAs($a)
        ->withSession(['errors' => ['default' => ['format' => ':message', 'messages' => ['rating' => ['กรุณาเลือกคะแนน']]]]])
        ->get($url)->assertOk()->getContent();
    // ข้อความ error และค่าเดิมอยู่ในฟอร์มของ b เท่านั้น
    expect(substr_count($html, 'กรุณาเลือกคะแนน'))->toBe(1);
    $hostCard = substr($html, strpos($html, 'id="person-'.$host->id.'"'), strpos($html, 'id="person-'.$b->id.'"') - strpos($html, 'id="person-'.$host->id.'"'));
    $bCard = substr($html, strpos($html, 'id="person-'.$b->id.'"'));
    expect($hostCard)->not->toContain('กรุณาเลือกคะแนน')->not->toContain('ข้อความค้างของบี');
    expect($bCard)->toContain('กรุณาเลือกคะแนน')->toContain('ข้อความค้างของบี');
});

// ──────────────── แก้ไข / ลบ ────────────────

test('เจ้าของรีวิวแก้ไขและลบรีวิวของตัวเองได้ และหน้า people แสดงรีวิวของฉัน', function () {
    [$activity, $host, $a] = urScenario();
    $review = urReview($activity, $a, $host, 'host', 2, 'ธรรมดา');

    $this->actingAs($a)->get(route('activities.people', $activity))->assertOk()
        ->assertSee('รีวิวของคุณ')->assertSee('ธรรมดา')->assertSee('บันทึกการแก้ไข')
        ->assertSee(route('activities.user-reviews.update', [$activity, $review]), false)
        ->assertDontSee(route('activities.people.review', [$activity, $host]), false);

    $this->actingAs($a)->patch(route('activities.user-reviews.update', [$activity, $review]), ['rating' => 5, 'comment' => 'ดีกว่าที่คิด'])
        ->assertSessionHas('success');
    expect($review->fresh()->rating)->toBe(5);
    expect($review->fresh()->comment)->toBe('ดีกว่าที่คิด');

    $this->actingAs($a)->patch(route('activities.user-reviews.update', [$activity, $review]), ['rating' => 0])
        ->assertSessionHasErrors('rating');
    expect($review->fresh()->rating)->toBe(5);

    $this->actingAs($a)->delete(route('activities.user-reviews.destroy', [$activity, $review]))->assertSessionHas('success');
    $this->assertDatabaseCount('user_reviews', 0);
});

test('แก้ไขหรือลบรีวิวของคนอื่นไม่ได้ รีวิวต้องเป็นของกิจกรรมใน URL และกิจกรรมที่ถูกซ่อนแก้ไขไม่ได้', function () {
    [$activity, $host, $a, $b] = urScenario();
    $review = urReview($activity, $a, $host, 'host', 4);
    $other = urActivity($host, 'กิจกรรมอื่น');

    $this->actingAs($b)->patch(route('activities.user-reviews.update', [$activity, $review]), ['rating' => 1])->assertForbidden();
    $this->actingAs($host)->delete(route('activities.user-reviews.destroy', [$activity, $review]))->assertForbidden();
    $this->actingAs($a)->patch(route('activities.user-reviews.update', [$other, $review]), ['rating' => 1])->assertNotFound();
    $this->actingAs($a)->delete(route('activities.user-reviews.destroy', [$other, $review]))->assertNotFound();

    $activity->hidden_at = now();
    $activity->save();
    $this->actingAs($a)->patch(route('activities.user-reviews.update', [$activity, $review]), ['rating' => 1])
        ->assertSessionHas('error');
    expect($review->fresh()->rating)->toBe(4);
});

// ──────────────── รายงานและ Admin ────────────────

test('รายงานรีวิวผู้ใช้ได้ แต่รายงานรีวิวของตัวเองหรือรายงานซ้ำไม่ได้', function () {
    [$activity, $host, $a, $b] = urScenario();
    $review = urReview($activity, $a, $b, 'participant', 1, 'คำไม่สุภาพ');

    $this->actingAs($b)->post(route('user-reviews.report', $review), ['user_review_reason' => 'รีวิวใช้คำหยาบ'])
        ->assertSessionHas('success');
    $this->assertDatabaseHas('reports', ['target_type' => 'user_review', 'target_id' => $review->id, 'reporter_id' => $b->id, 'status' => 'pending']);

    $this->actingAs($b)->post(route('user-reviews.report', $review), ['user_review_reason' => 'รีวิวใช้คำหยาบอีกครั้ง'])
        ->assertSessionHas('error');
    $this->actingAs($a)->post(route('user-reviews.report', $review), ['user_review_reason' => 'รายงานตัวเอง'])
        ->assertSessionHas('error', 'ไม่สามารถรายงานรีวิวของตัวเองได้');
    $this->actingAs($host)->post(route('user-reviews.report', $review), ['user_review_reason' => 'สั้น'])
        ->assertSessionHasErrors('user_review_reason');

    expect(Report::count())->toBe(1);
});

test('Admin ลบรีวิวผู้ใช้จากหน้าตรวจรายงาน: บันทึกประวัติ แจ้งผู้เขียน และปิดรายงานอื่นที่ค้าง', function () {
    [$activity, $host, $a, $b] = urScenario();
    $admin = urUser('ur-admin@test.com', 'admin');
    $review = urReview($activity, $a, $b, 'participant', 1, 'คำไม่สุภาพมาก');
    $this->actingAs($b)->post(route('user-reviews.report', $review), ['user_review_reason' => 'รีวิวใช้คำหยาบ']);
    $this->actingAs($host)->post(route('user-reviews.report', $review), ['user_review_reason' => 'ไม่สุภาพกับผู้เข้าร่วม']);
    [$first, $second] = Report::orderBy('id')->get()->all();

    $this->actingAs($admin)->get(route('admin.reports.index'))->assertOk()
        ->assertSee('รีวิวผู้ใช้')->assertSee('คำไม่สุภาพมาก')->assertSee($a->name)->assertSee($b->name)
        ->assertSee(route('activities.people', $activity), false);

    $this->actingAs($admin)->patch(route('admin.reports.resolve', $first), ['action' => 'hide', 'note' => 'ใช้คำไม่สุภาพ'])
        ->assertSessionHas('success');

    $this->assertDatabaseCount('user_reviews', 0);
    expect($first->fresh()->status)->toBe('actioned');
    expect($second->fresh()->status)->toBe('actioned');
    $log = ModerationLog::where('action', 'delete_user_review')->firstOrFail();
    expect($log->report_id)->toBe($first->id);
    expect($log->target_type)->toBe('user_review');
    expect($log->actionLabel())->toBe('ลบรีวิวผู้ใช้');
    expect($log->targetLabel())->toContain('(ลบแล้ว)');
    expect($a->fresh()->notifications->first()->data['message'])->toContain('ผู้ดูแลระบบลบรีวิว')->toContain('ใช้คำไม่สุภาพ');
});

test('Admin ลบรีวิวผู้ใช้โดยตรงพร้อมเหตุผล ส่วนนักศึกษาใช้ช่องทาง Admin ไม่ได้', function () {
    [$activity, $host, $a, $b] = urScenario();
    $admin = urUser('ur-admin@test.com', 'admin');
    $review = urReview($activity, $a, $b, 'participant', 1, 'รีวิวแย่');

    $this->actingAs($host)->delete(route('admin.user-reviews.destroy', $review), ['moderation_reason' => 'ลองลบ'])->assertForbidden();
    $this->actingAs($admin)->delete(route('admin.user-reviews.destroy', $review), ['moderation_reason' => ''])
        ->assertSessionHasErrors('moderation_reason');
    $this->assertDatabaseCount('user_reviews', 1);

    // ปุ่มลบของผู้ดูแลอยู่ในโปรไฟล์ของผู้ถูกรีวิว
    $this->actingAs($admin)->get(route('users.show', ['user' => $b, 'tab' => 'participant']))->assertOk()
        ->assertSee(route('admin.user-reviews.destroy', $review), false);
    $this->actingAs($host)->get(route('users.show', ['user' => $b, 'tab' => 'participant']))->assertOk()
        ->assertDontSee(route('admin.user-reviews.destroy', $review), false)
        ->assertSee(route('user-reviews.report', $review), false);

    $this->actingAs($admin)->delete(route('admin.user-reviews.destroy', $review), ['moderation_reason' => 'เนื้อหาไม่เหมาะสม'])
        ->assertSessionHas('success');
    $this->assertDatabaseCount('user_reviews', 0);
    $this->assertDatabaseHas('moderation_logs', ['action' => 'delete_user_review', 'target_id' => $review->id, 'report_id' => null]);
});

// ──────────────── โปรไฟล์ / คำขอ / นัดของฉัน / หน้ากิจกรรม ────────────────

test('โปรไฟล์แยกแท็บรีวิวที่ได้รับในฐานะผู้จัดและผู้เข้าร่วม', function () {
    [$activity, $host, $a, $b] = urScenario();
    $mine = urActivity($a, 'กิจกรรมที่เอจัด');
    urJoin($mine, $b);
    urReview($activity, $host, $a, 'participant', 4, 'มาตรงเวลา');
    urReview($activity, $b, $a, 'participant', 2, 'เล่นแรงไปหน่อย');
    urReview($mine, $b, $a, 'host', 5, 'จัดสนุกมาก');

    // ค่าเริ่มต้นคือแท็บผู้จัด
    $response = $this->actingAs($host)->get(route('users.show', $a))->assertOk()
        ->assertSee('จัดสนุกมาก')->assertDontSee('มาตรงเวลา')
        ->assertSee(route('users.show', ['user' => $a, 'tab' => 'participant']), false);
    expect($response->viewData('stats')['participant_rating'])->toBe(['rating' => 3.0, 'total' => 2]);
    expect($response->viewData('stats')['host_rating'])->toBe(['rating' => 5.0, 'total' => 1]);

    $this->actingAs($host)->get(route('users.show', ['user' => $a, 'tab' => 'participant']))->assertOk()
        ->assertSee('มาตรงเวลา')->assertSee('เล่นแรงไปหน่อย')->assertDontSee('จัดสนุกมาก')
        ->assertSee('3.0')->assertSee('จาก 2 รีวิว')
        ->assertSee($activity->title);

    // ไม่มีรีวิวในฐานะผู้จัด: เริ่มที่แท็บผู้เข้าร่วม
    $this->actingAs($a)->get(route('users.show', $b))->assertOk()->assertViewHas('tab', 'host');
    urReview($activity, $a, $b, 'participant', 5);
    $this->actingAs($a)->get(route('users.show', $b))->assertOk()->assertViewHas('tab', 'participant');
});

test('หน้าจัดการคำขอแสดงคะแนนผู้เข้าร่วมและสถิติมาตามนัดของผู้ขอ', function () {
    [$activity, $host, $a, $b] = urScenario();
    urReview($activity, $host, $a, 'participant', 5);
    urReview($activity, $b, $a, 'participant', 4);
    $upcoming = urActivity($host, 'กิจกรรมใหม่', ended: false);
    urJoin($upcoming, $a, 'pending', null);
    urJoin($upcoming, $b, 'pending', null);

    $response = $this->actingAs($host)->get(route('activities.requests', $upcoming))->assertOk()
        ->assertSee('4.5')->assertSee('(2 รีวิว)')->assertSee('มาตามนัด 1/1 ครั้ง')
        ->assertSee('ยังไม่มีรีวิวผู้เข้าร่วม');
    expect($response->viewData('ratings')->get($a->id))->toBe(['rating' => 4.5, 'total' => 2]);
    expect($response->viewData('attendance')->get($b->id))->toBe(['present' => 1, 'total' => 1]);
});

test('หน้ากิจกรรมและนัดของฉันนับงานรีวิวคนที่ค้าง', function () {
    [$activity, $host, $a, $b] = urScenario();

    // a ยังไม่รีวิวใครและยังไม่รีวิวกิจกรรม: ปุ่มให้คะแนนพาไปรีวิวกิจกรรมก่อน (1 + 2 คน)
    $this->actingAs($a)->get(route('activities.show', $activity))->assertOk()
        ->assertSee('รีวิวเพื่อนร่วมกิจกรรม')->assertViewHas('peopleToReview', 2)
        ->assertSee(route('activities.people', $activity), false);
    $this->actingAs($a)->get(route('my-activities.index'))->assertOk()
        ->assertSee('ให้คะแนน')->assertSee(route('activities.show', $activity).'#reviews', false)
        ->assertSee('รีวิวค้าง 3 รายการ');

    // รีวิวกิจกรรมแล้ว: ลิงก์ไปหน้า people
    $this->actingAs($a)->post(route('activities.reviews.store', $activity), ['rating' => 5]);
    $this->actingAs($a)->get(route('my-activities.index'))->assertOk()
        ->assertSee(route('activities.people', $activity), false)->assertSee('รีวิวค้าง 2 รายการ');

    // ผู้จัดเห็นงานรีวิวผู้เข้าร่วมที่มา 2 คน
    $this->actingAs($host)->get(route('activities.show', $activity))->assertOk()->assertViewHas('peopleToReview', 2);
    $this->actingAs($host)->get(route('my-activities.index'))->assertOk()
        ->assertSee(route('activities.people', $activity), false)->assertSee('รีวิวค้าง 2 รายการ');

    urReview($activity, $host, $a, 'participant', 5);
    urReview($activity, $host, $b, 'participant', 5);
    $this->actingAs($host)->get(route('activities.show', $activity))->assertViewHas('peopleToReview', 0);
    $this->actingAs($host)->get(route('my-activities.index'))->assertOk()->assertDontSee('รีวิวค้าง');

    // กิจกรรมที่ยังไม่จบ ไม่มีปุ่มรีวิวเพื่อน
    $future = urActivity($host, 'ยังไม่จบ', ended: false);
    $this->actingAs($host)->get(route('activities.show', $future))->assertOk()
        ->assertViewHas('peopleToReview', null)->assertDontSee(route('activities.people', $future), false);
});

test('งานรีวิวค้างยังนับถูกเมื่อแก้คนที่รีวิวแล้วเป็นขาด ทั้งผู้จัดและผู้เข้าร่วม', function () {
    [$activity, $host, $a, $b] = urScenario();
    urReview($activity, $host, $a, 'participant', 5);
    urReview($activity, $b, $a, 'participant', 4);
    $this->actingAs($b)->post(route('activities.reviews.store', $activity), ['rating' => 5]);
    $aParticipation = $activity->participants()->where('user_id', $a->id)->firstOrFail();
    $aParticipation->update(['attendance' => 'absent']);

    foreach ([$host, $b] as $reviewer) {
        $this->actingAs($reviewer)->get(route('activities.show', $activity))
            ->assertOk()->assertViewHas('peopleToReview', 1);
        $response = $this->actingAs($reviewer)->get(route('my-activities.index'))->assertOk()
            ->assertSee('รีวิวค้าง 1 รายการ')
            ->assertSee(route('activities.people', $activity), false);
        expect($response->viewData('pendingPeople')[$activity->id])->toBe(1);
    }
});

test('แก้ไขและลบรีวิวเดิมได้หลังแก้การเช็กชื่อ โดยไม่เปิดสิทธิ์สร้างรีวิวใหม่', function () {
    [$activity, $host, $a, $b] = urScenario();
    $hostReview = urReview($activity, $host, $a, 'participant', 5, 'ความคิดเห็นเดิมของผู้จัด');
    $participantReview = urReview($activity, $a, $host, 'host', 4, 'ความคิดเห็นเดิมของผู้เข้าร่วม');
    $activity->participants()->where('user_id', $a->id)->firstOrFail()->update(['attendance' => 'absent']);

    $this->actingAs($host)->get(route('activities.people', $activity))->assertOk()
        ->assertSee('ความคิดเห็นเดิมของผู้จัด')
        ->assertSee(route('activities.user-reviews.update', [$activity, $hostReview]), false);
    $this->actingAs($a)->get(route('activities.people', $activity))->assertOk()
        ->assertSee('ความคิดเห็นเดิมของผู้เข้าร่วม')
        ->assertSee(route('activities.user-reviews.update', [$activity, $participantReview]), false)
        ->assertDontSee('ส่งรีวิวผู้ใช้');
    $this->actingAs($a)->post(route('activities.people.review', [$activity, $b]), ['rating' => 5])
        ->assertSessionHas('error');
    $this->actingAs($a)->patch(route('activities.user-reviews.update', [$activity, $participantReview]), ['rating' => 3])
        ->assertSessionHas('success');
    $this->actingAs($a)->followingRedirects()->from(route('activities.people', $activity))
        ->delete(route('activities.user-reviews.destroy', [$activity, $participantReview]))
        ->assertOk()->assertSee('ลบรีวิวเรียบร้อยแล้ว');
    $this->assertDatabaseMissing('user_reviews', ['id' => $participantReview->id]);
});

<?php

use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\Category;
use App\Models\Report;
use App\Models\User;
use App\Notifications\ActivityNotification;

// ──────────────── Helpers (ชื่อขึ้นต้น nt เพื่อไม่ชนกับ helper ในไฟล์เทสต์อื่น) ────────────────

function ntUser(string $name, string $role = 'student'): User
{
    return User::factory()->create(['name' => $name, 'role' => $role, 'status' => 'active']);
}

/** กิจกรรมที่ยังไม่เริ่ม (เริ่มอีก 2 วัน) ใช้ payload เดียวกับฟอร์มเพื่อทดสอบการแก้ไข */
function ntPayload(Category $category, array $override = []): array
{
    return [
        'title' => 'ชวนเล่นแบดมินตัน',
        'description' => 'หาเพื่อนเล่นหลังเลิกเรียน',
        'category_id' => $category->id,
        'location' => 'สนามกีฬา',
        'starts_at' => now()->addDays(2)->startOfMinute()->format('Y-m-d\TH:i'),
        'ends_at' => now()->addDays(2)->addHours(2)->startOfMinute()->format('Y-m-d\TH:i'),
        'capacity' => 4,
        ...$override,
    ];
}

function ntActivity(User $host): Activity
{
    $activity = new Activity(ntPayload(Category::firstOrCreate(['name' => 'กีฬา'])));
    $activity->user()->associate($host);
    $activity->status = 'published';
    $activity->save();

    return $activity;
}

function ntJoin(Activity $activity, User $user, string $status = 'pending'): ActivityParticipant
{
    $p = new ActivityParticipant;
    $p->activity()->associate($activity);
    $p->user()->associate($user);
    $p->status = $status;
    $p->save();

    return $p;
}

function ntLast(User $user): ?array
{
    return $user->fresh()->notifications()->first()?->data;
}

// ──────────────── คำขอเข้าร่วม ────────────────

test('ผู้จัดได้รับแจ้งเตือนเมื่อมีคนขอเข้าร่วม พร้อมลิงก์ไปหน้าจัดการคำขอ', function () {
    $host = ntUser('Host');
    $guest = ntUser('Guest Nong');
    $activity = ntActivity($host);

    $this->actingAs($guest)->post(route('activities.join', $activity), ['message' => 'ขอเล่นด้วย'])->assertRedirect();

    expect($host->fresh()->notifications)->toHaveCount(1);
    expect(ntLast($host)['message'])->toContain('Guest Nong')->toContain('ชวนเล่นแบดมินตัน');
    expect(ntLast($host)['url'])->toBe(route('activities.requests', $activity, false));
    expect(ntLast($host)['kind'])->toBe('request');
    expect($guest->fresh()->notifications)->toHaveCount(0);
});

test('ผู้จัดได้รับแจ้งเตือนเมื่อมีคนถอนคำขอหรือถอนตัว', function () {
    $host = ntUser('Host');
    $a = ntUser('Pending A');
    $b = ntUser('Approved B');
    $activity = ntActivity($host);
    ntJoin($activity, $a);
    ntJoin($activity, $b, 'approved');

    $this->actingAs($a)->patch(route('activities.cancel-request', $activity))->assertRedirect();
    expect(ntLast($host)['message'])->toContain('Pending A ถอนคำขอ');

    $this->travel(1)->minutes(); // แยกเวลาให้การแจ้งเตือนเรียงลำดับได้แน่นอน
    $this->actingAs($b)->patch(route('activities.cancel-request', $activity))->assertRedirect();
    expect(ntLast($host)['message'])->toContain('Approved B ถอนตัว');
    expect($host->fresh()->notifications)->toHaveCount(2);
});

test('ผู้ขอได้รับแจ้งเตือนเมื่ออนุมัติหรือปฏิเสธ พร้อมลิงก์ไปหน้ากิจกรรม', function () {
    $host = ntUser('Host');
    $a = ntUser('A');
    $b = ntUser('B');
    $activity = ntActivity($host);
    $pa = ntJoin($activity, $a);
    $pb = ntJoin($activity, $b);

    $this->actingAs($host)->patch(route('activities.requests.approve', [$activity, $pa]))->assertRedirect();
    $this->patch(route('activities.requests.reject', [$activity, $pb]))->assertRedirect();

    expect(ntLast($a))->toMatchArray(['kind' => 'success', 'url' => route('activities.show', $activity, false)]);
    expect(ntLast($b))->toMatchArray(['kind' => 'danger', 'url' => route('activities.show', $activity, false)]);
});

// ──────────────── ความเคลื่อนไหวของกิจกรรม ────────────────

test('ยกเลิกกิจกรรมแล้วผู้ที่รออนุมัติและได้รับอนุมัติได้รับแจ้งเตือน แต่คนที่ถูกปฏิเสธไม่ได้รับ', function () {
    $host = ntUser('Host');
    [$pending, $approved, $rejected] = [ntUser('P'), ntUser('A'), ntUser('R')];
    $activity = ntActivity($host);
    ntJoin($activity, $pending);
    ntJoin($activity, $approved, 'approved');
    ntJoin($activity, $rejected, 'rejected');

    $this->actingAs($host)->patch(route('activities.cancel', $activity))->assertRedirect();

    expect(ntLast($pending)['message'])->toContain('ถูกยกเลิก');
    expect(ntLast($approved)['message'])->toContain('ถูกยกเลิก');
    expect($rejected->fresh()->notifications)->toHaveCount(0);
    expect($host->fresh()->notifications)->toHaveCount(0);
});

test('แก้วันเวลาหรือสถานที่แล้วผู้เข้าร่วมได้รับแจ้งเตือน แต่แก้แค่ชื่อหรือรายละเอียดไม่แจ้ง', function () {
    $host = ntUser('Host');
    $member = ntUser('Member');
    $activity = ntActivity($host);
    ntJoin($activity, $member, 'approved');
    $category = Category::firstOrCreate(['name' => 'กีฬา']);

    $this->actingAs($host)->put(route('activities.update', $activity), ntPayload($category, ['title' => 'ชื่อใหม่', 'description' => 'รายละเอียดใหม่']))->assertRedirect();
    expect($member->fresh()->notifications)->toHaveCount(0);

    $this->put(route('activities.update', $activity), ntPayload($category, ['title' => 'ชื่อใหม่', 'location' => 'โรงยิมใหม่']))->assertRedirect();
    expect($member->fresh()->notifications)->toHaveCount(1);
    expect(ntLast($member)['message'])->toContain('เปลี่ยนวันเวลาหรือสถานที่');
});

test('Admin ซ่อนและเลิกซ่อนกิจกรรมแล้วผู้จัดได้รับแจ้งเตือน', function () {
    $host = ntUser('Host');
    $reporter = ntUser('Reporter');
    $admin = ntUser('Admin', 'admin');
    $activity = ntActivity($host);
    $report = new Report(['target_type' => 'activity', 'target_id' => $activity->id, 'reason' => 'เนื้อหาไม่เหมาะสม']);
    $report->reporter()->associate($reporter);
    $report->save();

    $this->actingAs($admin)->patch(route('admin.reports.resolve', $report), ['action' => 'hide', 'note' => 'ผิดกฎชุมชน'])->assertRedirect();
    expect(ntLast($host)['message'])->toContain('ถูกซ่อน')->toContain('ผิดกฎชุมชน');

    $this->travel(1)->minutes();
    $this->patch(route('admin.activities.unhide', $activity), ['note' => 'แก้ไขแล้ว'])->assertRedirect();
    expect(ntLast($host)['message'])->toContain('กลับมาแสดงตามปกติ');
});

// ──────────────── หน้าการแจ้งเตือน ────────────────

test('กดการแจ้งเตือนแล้วอ่านแล้วและพาไปหน้าที่เกี่ยวข้อง', function () {
    $host = ntUser('Host');
    $guest = ntUser('Guest');
    $activity = ntActivity($host);
    $this->actingAs($guest)->post(route('activities.join', $activity))->assertRedirect();

    $notification = $host->fresh()->notifications()->first();
    $this->actingAs($host)->get(route('notifications.index'))
        ->assertOk()
        ->assertSee(route('notifications.open', $notification->id), false)
        ->assertSee('อ่านทั้งหมดแล้ว (1)');

    $this->get(route('notifications.open', $notification->id))
        ->assertRedirect(route('activities.requests', $activity, false));
    expect($notification->fresh()->read_at)->not->toBeNull();
    expect($host->fresh()->unreadNotifications()->count())->toBe(0);
});

test('เปิดการแจ้งเตือนของคนอื่นไม่ได้ และลิงก์ภายนอกไม่ถูก redirect', function () {
    $host = ntUser('Host');
    $other = ntUser('Other');
    $activity = ntActivity($host);
    $this->actingAs($other)->post(route('activities.join', $activity));
    $notification = $host->fresh()->notifications()->first();

    $this->actingAs($other)->get(route('notifications.open', $notification->id))->assertNotFound();
    expect($notification->fresh()->read_at)->toBeNull();

    // การแจ้งเตือนที่มี url ภายนอก (เช่น ข้อมูลเสีย) ต้องกลับมาหน้ารายการแทน
    $notification->update(['data' => ['message' => 'x', 'url' => '//evil.example']]);
    $this->actingAs($host)->get(route('notifications.open', $notification->id))->assertRedirect(route('notifications.index'));
});

test('การแจ้งเตือนเก่าที่ไม่มี url และ kind ยังแสดงได้ และปุ่มอ่านทั้งหมดใช้งานได้', function () {
    $user = ntUser('Old');
    $user->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => ActivityNotification::class,
        'data' => ['message' => "คำขอเข้าร่วมกิจกรรม 'เก่า' ของคุณได้รับการอนุมัติแล้ว!"],
    ]);

    $this->actingAs($user)->get(route('notifications.index'))->assertOk()->assertSee('เก่า');
    $id = $user->notifications()->first()->id;
    $this->get(route('notifications.open', $id))->assertRedirect(route('notifications.index'));

    $user->notifications()->create(['id' => (string) Str::uuid(), 'type' => 'x', 'data' => ['message' => 'ใหม่']]);
    $this->post(route('notifications.markAsRead'))->assertRedirect();
    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
});

test('หน้าการแจ้งเตือนแบ่งหน้าละ 20 รายการ ใหม่สุดก่อน', function () {
    $user = ntUser('Busy');
    foreach (range(1, 25) as $i) {
        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'x',
            'data' => ['message' => "แจ้งเตือนที่ {$i}"],
            'created_at' => now()->subMinutes(30 - $i),
        ]);
    }

    $this->actingAs($user)->get(route('notifications.index'))->assertOk()
        ->assertSee('แจ้งเตือนที่ 25')->assertDontSee('แจ้งเตือนที่ 5<');
    $this->get(route('notifications.index', ['page' => 2]))->assertOk()->assertSee('แจ้งเตือนที่ 1<', false);
});

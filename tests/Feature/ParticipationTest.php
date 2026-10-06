<?php

use App\Models\Activity;
use App\Models\ActivityParticipant;
use App\Models\Category;
use App\Models\User;

// ──────────────── Helpers ────────────────

function createPublishedActivity(User $host, int $capacity = 5): Activity
{
    $category = Category::firstOrCreate(['name' => 'กีฬา']);

    $activity = new Activity([
        'category_id' => $category->id,
        'title' => 'กิจกรรมทดสอบ',
        'description' => 'รายละเอียดทดสอบ',
        'location' => 'สนามกีฬา',
        'starts_at' => now()->addDays(3),
        'ends_at' => now()->addDays(3)->addHours(2),
        'capacity' => $capacity,
    ]);
    $activity->user()->associate($host);
    $activity->status = 'published';
    $activity->save();

    return $activity;
}

function createUser(string $email = 'user@test.com'): User
{
    return User::create([
        'name' => 'Test User',
        'email' => $email,
        'password' => 'password',
        'role' => 'student',
        'status' => 'active',
    ]);
}

// ──────────────── ส่งคำขอเข้าร่วม ────────────────

test('ผู้ใช้ส่งคำขอเข้าร่วมกิจกรรมสำเร็จ', function () {
    $host = createUser('host@test.com');
    $user = createUser('joiner@test.com');
    $activity = createPublishedActivity($host);

    $response = $this->actingAs($user)->post(route('activities.join', $activity), [
        'message' => 'อยากร่วมด้วยครับ',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('activity_participants', [
        'activity_id' => $activity->id,
        'user_id' => $user->id,
        'status' => 'pending',
        'message' => 'อยากร่วมด้วยครับ',
    ]);
});

test('ป้องกันคำขอซ้ำ — ผู้ใช้ที่มีคำขอ pending อยู่แล้วส่งซ้ำไม่ได้', function () {
    $host = createUser('host@test.com');
    $user = createUser('joiner@test.com');
    $activity = createPublishedActivity($host);

    // ส่งคำขอครั้งแรก
    $this->actingAs($user)->post(route('activities.join', $activity));

    // ส่งคำขอซ้ำ
    $response = $this->actingAs($user)->post(route('activities.join', $activity));
    $response->assertSessionHas('error');

    // ต้องมีแค่ 1 record
    expect(ActivityParticipant::where('activity_id', $activity->id)->where('user_id', $user->id)->count())->toBe(1);
});

test('ป้องกัน Host ขอเข้าร่วมกิจกรรมตัวเอง', function () {
    $host = createUser('host@test.com');
    $activity = createPublishedActivity($host);

    $response = $this->actingAs($host)->post(route('activities.join', $activity));
    $response->assertForbidden();
});

test('ป้องกันขอเข้าร่วมกิจกรรมที่ยกเลิกแล้ว', function () {
    $host = createUser('host@test.com');
    $user = createUser('joiner@test.com');
    $activity = createPublishedActivity($host);
    $activity->status = 'cancelled';
    $activity->save();

    $response = $this->actingAs($user)->post(route('activities.join', $activity));
    $response->assertForbidden();
});

test('ป้องกันขอเข้าร่วมกิจกรรมที่หมดเวลาแล้ว', function () {
    $host = createUser('host@test.com');
    $user = createUser('joiner@test.com');
    $category = Category::firstOrCreate(['name' => 'กีฬา']);

    $activity = new Activity([
        'category_id' => $category->id,
        'title' => 'กิจกรรมเก่า',
        'description' => 'หมดเวลาแล้ว',
        'location' => 'ที่ไหนสักที่',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->subDay()->addHours(2),
        'capacity' => 5,
    ]);
    $activity->user()->associate($host);
    $activity->status = 'published';
    $activity->save();

    $response = $this->actingAs($user)->post(route('activities.join', $activity));
    $response->assertForbidden();
});

test('ป้องกันส่งคำขอเมื่อกิจกรรมเต็ม', function () {
    $host = createUser('host@test.com');
    $activity = createPublishedActivity($host, capacity: 1);

    // เติมที่ว่างให้เต็ม
    $filler = createUser('filler@test.com');
    $p = new ActivityParticipant;
    $p->activity_id = $activity->id;
    $p->user_id = $filler->id;
    $p->status = 'approved';
    $p->save();

    $user = createUser('joiner@test.com');
    $response = $this->actingAs($user)->post(route('activities.join', $activity));
    $response->assertSessionHas('error');
});

// ──────────────── Host อนุมัติ / ปฏิเสธ ────────────────

test('Host อนุมัติคำขอสำเร็จ', function () {
    $host = createUser('host@test.com');
    $user = createUser('joiner@test.com');
    $activity = createPublishedActivity($host);

    $participant = new ActivityParticipant;
    $participant->activity_id = $activity->id;
    $participant->user_id = $user->id;
    $participant->status = 'pending';
    $participant->save();

    $response = $this->actingAs($host)->patch(route('activities.requests.approve', [$activity, $participant]));
    $response->assertRedirect();
    $response->assertSessionHas('success');

    $participant->refresh();
    expect($participant->status)->toBe('approved');
});

test('Host ปฏิเสธคำขอสำเร็จ', function () {
    $host = createUser('host@test.com');
    $user = createUser('joiner@test.com');
    $activity = createPublishedActivity($host);

    $participant = new ActivityParticipant;
    $participant->activity_id = $activity->id;
    $participant->user_id = $user->id;
    $participant->status = 'pending';
    $participant->save();

    $response = $this->actingAs($host)->patch(route('activities.requests.reject', [$activity, $participant]));
    $response->assertRedirect();

    $participant->refresh();
    expect($participant->status)->toBe('rejected');
});

test('ป้องกันอนุมัติเกิน capacity — เต็มแล้วรับเพิ่มไม่ได้', function () {
    $host = createUser('host@test.com');
    $activity = createPublishedActivity($host, capacity: 1);

    // เติมให้เต็ม
    $filler = createUser('filler@test.com');
    $pFiller = new ActivityParticipant;
    $pFiller->activity_id = $activity->id;
    $pFiller->user_id = $filler->id;
    $pFiller->status = 'approved';
    $pFiller->save();

    // สร้างคำขอ pending อีกคน
    $user = createUser('joiner@test.com');
    $pUser = new ActivityParticipant;
    $pUser->activity_id = $activity->id;
    $pUser->user_id = $user->id;
    $pUser->status = 'pending';
    $pUser->save();

    $response = $this->actingAs($host)->patch(route('activities.requests.approve', [$activity, $pUser]));
    $response->assertSessionHas('error');

    $pUser->refresh();
    expect($pUser->status)->toBe('pending');
});

test('ผู้ใช้ที่ไม่ใช่ Host ไม่สามารถอนุมัติคำขอได้', function () {
    $host = createUser('host@test.com');
    $user = createUser('joiner@test.com');
    $stranger = createUser('stranger@test.com');
    $activity = createPublishedActivity($host);

    $participant = new ActivityParticipant;
    $participant->activity_id = $activity->id;
    $participant->user_id = $user->id;
    $participant->status = 'pending';
    $participant->save();

    $response = $this->actingAs($stranger)->patch(route('activities.requests.approve', [$activity, $participant]));
    $response->assertForbidden();
});

// ──────────────── ถอนคำขอ / ถอนตัว ────────────────

test('ถอนคำขอ pending สำเร็จ', function () {
    $host = createUser('host@test.com');
    $user = createUser('joiner@test.com');
    $activity = createPublishedActivity($host);

    $participant = new ActivityParticipant;
    $participant->activity_id = $activity->id;
    $participant->user_id = $user->id;
    $participant->status = 'pending';
    $participant->save();

    $response = $this->actingAs($user)->patch(route('activities.cancel-request', $activity));
    $response->assertRedirect();
    $response->assertSessionHas('success');

    $participant->refresh();
    expect($participant->status)->toBe('cancelled');
});

test('ถอนตัวจาก approved — ที่ว่างกลับมา', function () {
    $host = createUser('host@test.com');
    $user = createUser('joiner@test.com');
    $activity = createPublishedActivity($host, capacity: 1);

    $participant = new ActivityParticipant;
    $participant->activity_id = $activity->id;
    $participant->user_id = $user->id;
    $participant->status = 'approved';
    $participant->save();

    expect($activity->isFull())->toBeTrue();

    $this->actingAs($user)->patch(route('activities.cancel-request', $activity));

    $participant->refresh();
    expect($participant->status)->toBe('cancelled');
    expect($activity->isFull())->toBeFalse();
    expect($activity->remainingSlots())->toBe(1);
});

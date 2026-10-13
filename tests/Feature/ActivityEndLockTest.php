<?php

use App\Models\Activity;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-10-14 12:00:00', 'Asia/Bangkok'));
    Storage::fake('public');
    $this->owner = User::factory()->create();
    $this->activity = new Activity;
    $this->activity->forceFill([
        'user_id' => $this->owner->id, 'category_id' => Category::create(['name' => 'กีฬา'])->id,
        'title' => 'บาสที่จบแล้ว', 'description' => 'รายละเอียดเดิม', 'location' => 'สนามเดิม',
        'google_maps_url' => 'https://maps.app.goo.gl/original',
        'location_image_path' => UploadedFile::fake()->image('court.jpg')->store('activity-locations', 'public'),
        'starts_at' => now()->subHours(2), 'ends_at' => now()->subHour(), 'capacity' => 5, 'status' => 'published',
    ])->save();
    $this->actingAs($this->owner);
    $this->futurePayload = [
        'category_id' => $this->activity->category_id, 'title' => 'เปิดกิจกรรมเก่าอีกครั้ง',
        'description' => 'รายละเอียดใหม่', 'location' => 'สนามใหม่', 'capacity' => 10,
        'google_maps_url' => 'https://maps.app.goo.gl/replacement',
        'starts_at' => now()->addDay()->format('Y-m-d\TH:i'),
        'ends_at' => now()->addDay()->addHour()->format('Y-m-d\TH:i'),
    ];
});

test('ended activities cannot be edited reopened cancelled or have their photo deleted', function (int $minutesAgo) {
    $this->activity->ends_at = now()->subMinutes($minutesAgo);
    $this->activity->save();
    $original = $this->activity->fresh()->getAttributes();
    $path = $this->activity->location_image_path;

    $this->get(route('activities.show', $this->activity))->assertOk()
        ->assertSee('กิจกรรมจบแล้ว จึงล็อกการแก้ไขรายละเอียดและการยกเลิก')
        ->assertDontSee(route('activities.edit', $this->activity), false)
        ->assertDontSee('id="cancel-activity-form"', false)
        ->assertDontSee('id="cancel-activity-dialog"', false)
        ->assertSee('เช็กชื่อผู้เข้าร่วม');
    $this->get(route('activities.edit', $this->activity))->assertStatus(409);
    $this->putJson(route('activities.update', $this->activity), [
        ...$this->futurePayload, 'location_image' => UploadedFile::fake()->image('replacement.png'),
    ])->assertStatus(409);
    $this->patchJson(route('activities.update', $this->activity), $this->futurePayload)->assertStatus(409);
    $this->patchJson(route('activities.cancel', $this->activity))->assertStatus(409);
    $this->deleteJson(route('activities.location-image.destroy', $this->activity))->assertStatus(409);

    expect($this->activity->fresh()->getAttributes())->toBe($original);
    Storage::disk('public')->assertExists($path);
    expect(Storage::disk('public')->allFiles())->toBe([$path]);
})->with(['already ended' => 60, 'exactly at end time' => 0]);

test('owner can still view participants and take attendance after the event ends', function () {
    $participant = $this->activity->participants()->make(['status' => 'approved']);
    $participant->user()->associate(User::factory()->create());
    $participant->save();

    $this->get(route('activities.requests', $this->activity))->assertOk();
    $this->get(route('my-activities.index'))->assertOk()
        ->assertSee($this->activity->title)->assertSee('เช็กชื่อผู้เข้าร่วม');
    $this->patch(route('activities.requests.attendance', [$this->activity, $participant]), ['attendance' => 'present'])
        ->assertRedirect()->assertSessionHasNoErrors();
    expect($participant->fresh()->attendance)->toBe('present');
});

test('a form opened before the end time cannot change an activity after it ends', function () {
    $this->activity->ends_at = now()->addMinute();
    $this->activity->save();
    $this->get(route('activities.edit', $this->activity))->assertOk();
    $this->travelTo($this->activity->ends_at);

    $this->putJson(route('activities.update', $this->activity), $this->futurePayload)->assertStatus(409);
    expect($this->activity->fresh()->title)->toBe('บาสที่จบแล้ว');
    expect($this->activity->fresh()->status)->toBe('published');
});

test('other users and admins still cannot mutate someone elses ended activity', function (string $role) {
    $this->actingAs(User::factory()->create(['role' => $role]));
    $this->get(route('activities.edit', $this->activity))->assertForbidden();
    $this->putJson(route('activities.update', $this->activity), $this->futurePayload)->assertForbidden();
    $this->patchJson(route('activities.cancel', $this->activity))->assertForbidden();
    $this->deleteJson(route('activities.location-image.destroy', $this->activity))->assertForbidden();
    expect($this->activity->fresh()->status)->toBe('published');
})->with(['student', 'admin']);

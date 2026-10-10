<?php

use App\Models\Activity;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->owner = User::factory()->create();
    $this->payload = [
        'title' => 'บาสที่สนามใหม่',
        'description' => 'นัดเล่นบาส',
        'category_id' => Category::create(['name' => 'กีฬา'])->id,
        'location' => 'สนามบาส',
        'starts_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
        'ends_at' => now()->addDays(2)->addHours(2)->format('Y-m-d\TH:i'),
        'capacity' => 1,
    ];
});

test('owner can upload a location photo and maps link then replace and remove them', function () {
    $this->actingAs($this->owner)->post(route('activities.store'), [
        ...$this->payload,
        'location_image' => UploadedFile::fake()->image('court.jpg'),
        'google_maps_url' => 'https://maps.app.goo.gl/examplePlace',
    ])->assertRedirect();
    $activity = Activity::firstOrFail();
    $oldPath = $activity->location_image_path;
    Storage::disk('public')->assertExists($oldPath);
    expect($activity->google_maps_url)->toBe('https://maps.app.goo.gl/examplePlace');
    $this->get(route('activities.show', $activity))->assertOk()
        ->assertSee('storage/'.$oldPath)->assertSee('href="https://maps.app.goo.gl/examplePlace"', false)
        ->assertSee('เปิดสถานที่ใน Google Maps')->assertDontSee('id="location-map"', false);
    $this->get(route('activities.edit', $activity))->assertOk()
        ->assertSee('value="https://maps.app.goo.gl/examplePlace"', false)
        ->assertDontSee('id="longitude"', false)->assertDontSee('id="latitude"', false)
        ->assertSee('ลิงก์สถานที่ใน Google Maps');
    $this->put(route('activities.update', $activity), [
        ...$this->payload,
        'location_image' => UploadedFile::fake()->image('new-court.png'),
        'google_maps_url' => 'https://www.google.com/maps/place/Bangkok/',
    ])->assertRedirect();
    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($activity->fresh()->location_image_path);
    $newPath = $activity->fresh()->location_image_path;
    expect($activity->fresh()->google_maps_url)->toBe('https://www.google.com/maps/place/Bangkok/');
    $this->put(route('activities.update', $activity), [
        ...$this->payload, 'remove_location_image' => 1, 'google_maps_url' => '',
    ])->assertRedirect();
    Storage::disk('public')->assertMissing($newPath);
    expect($activity->fresh()->location_image_path)->toBeNull();
    expect($activity->fresh()->google_maps_url)->toBeNull();
    $this->get(route('activities.show', $activity))->assertDontSee('เปิดสถานที่ใน Google Maps');
});

test('invalid photos and non maps links are rejected', function (array $extra, string $field) {
    $this->actingAs($this->owner)->post(route('activities.store'), [...$this->payload, ...$extra])
        ->assertSessionHasErrors($field);
    expect(Activity::count())->toBe(0);
    expect(Storage::disk('public')->allFiles())->toBe([]);
})->with([
    'not an image' => [fn () => ['location_image' => UploadedFile::fake()->create('script.txt', 1, 'text/plain')], 'location_image'],
    'too large' => [fn () => ['location_image' => UploadedFile::fake()->image('court.jpg')->size(5121)], 'location_image'],
    'other website' => [['google_maps_url' => 'https://example.com/maps'], 'google_maps_url'],
    'javascript' => [['google_maps_url' => 'javascript:alert(1)'], 'google_maps_url'],
    'fake google host' => [['google_maps_url' => 'https://www.google.com.evil.example/maps'], 'google_maps_url'],
    'non maps google link' => [['google_maps_url' => 'https://www.google.com/search?q=test'], 'google_maps_url'],
    'insecure link' => [['google_maps_url' => 'http://maps.app.goo.gl/example'], 'google_maps_url'],
    'credentials in link' => [['google_maps_url' => 'https://user:pass@www.google.com/maps'], 'google_maps_url'],
]);

test('another user cannot replace the location photo or maps link', function () {
    $this->actingAs($this->owner)->post(route('activities.store'), [
        ...$this->payload, 'location_image' => UploadedFile::fake()->image('court.jpg'), 'google_maps_url' => 'https://maps.app.goo.gl/example',
    ])->assertRedirect();
    $activity = Activity::firstOrFail();
    $path = $activity->location_image_path;
    $this->actingAs(User::factory()->create())->put(route('activities.update', $activity), [
        ...$this->payload, 'remove_location_image' => 1, 'google_maps_url' => 'https://maps.app.goo.gl/other',
    ])->assertForbidden();
    expect($activity->fresh()->location_image_path)->toBe($path);
    expect($activity->fresh()->google_maps_url)->toBe('https://maps.app.goo.gl/example');
    Storage::disk('public')->assertExists($path);
});

test('old pins still open in google maps and the owner can clear the link', function () {
    $this->actingAs($this->owner)->post(route('activities.store'), $this->payload)->assertRedirect();
    $activity = Activity::firstOrFail();
    $activity->forceFill(['latitude' => 13.75, 'longitude' => 100.5])->save();
    $this->get(route('activities.show', $activity))->assertSee('destination=13.75,100.5')->assertSee('เปิดสถานที่ใน Google Maps');
    $this->put(route('activities.update', $activity), [...$this->payload, 'google_maps_url' => ''])->assertRedirect();
    expect($activity->fresh()->googleMapsUrl())->toBeNull();
});

test('google maps sharing link formats are supported', function (string $url) {
    $this->actingAs($this->owner)->post(route('activities.store'), [...$this->payload, 'google_maps_url' => $url])->assertRedirect();
    expect(Activity::firstOrFail()->google_maps_url)->toBe($url);
})->with([
    'https://maps.app.goo.gl/example',
    'https://www.google.com/maps/place/Bangkok/',
    'https://www.google.co.th/maps?q=Bangkok',
    'https://maps.google.com/?q=Bangkok',
    'https://goo.gl/maps/example',
]);

test('24 hour form fields save as Bangkok local time', function () {
    $date = now('Asia/Bangkok')->addDays(2)->format('Y-m-d');
    $payload = Illuminate\Support\Arr::except($this->payload, ['starts_at', 'ends_at']);
    $this->actingAs($this->owner)->post(route('activities.store'), [
        ...$payload, 'starts_at_date' => $date, 'starts_at_time' => '14:30',
        'ends_at_date' => $date, 'ends_at_time' => '16:00',
    ])->assertRedirect();
    $activity = Activity::firstOrFail();
    expect($activity->starts_at->format('Y-m-d H:i'))->toBe($date.' 14:30');
    expect($activity->ends_at->format('Y-m-d H:i'))->toBe($date.' 16:00');
    expect($activity->starts_at->timezoneName)->toBe('Asia/Bangkok');
    $this->get(route('activities.edit', $activity))->assertSee('value="14:30"', false)->assertDontSee('datetime-local');
});

test('24 hour form rejects AM PM and invalid clock values', function (string $time) {
    $date = now()->addDays(2)->format('Y-m-d');
    $this->actingAs($this->owner)->post(route('activities.store'), [
        ...$this->payload, 'starts_at_date' => $date, 'starts_at_time' => $time,
        'ends_at_date' => $date, 'ends_at_time' => '23:59',
    ])->assertSessionHasErrors('starts_at_time');
    expect(Activity::count())->toBe(0);
})->with(['02:30 PM', '24:00', '12:60']);

test('new activity time defaults follow Bangkok time across midnight', function () {
    $this->travelTo(Illuminate\Support\Carbon::parse('2026-10-10 23:30:00', 'Asia/Bangkok'));
    $this->actingAs($this->owner)->get(route('activities.create'))->assertOk()
        ->assertSee('value="2026-10-11"', false)->assertSee('value="00:30"', false)
        ->assertSee('value="01:30"', false)->assertDontSee('datetime-local');
});

test('availability filters count approved members and combine with the other filters', function () {
    $this->actingAs($this->owner)->post(route('activities.store'), $this->payload)->assertRedirect();
    $activity = Activity::firstOrFail();
    $member = User::factory()->create();
    $participant = $activity->participants()->make(['status' => 'pending']);
    $participant->user()->associate($member);
    $participant->save();
    $this->get(route('activities.index', ['status' => 'available']))->assertSee($activity->title);
    $this->get(route('activities.index', ['status' => 'full']))->assertDontSee($activity->title);
    $participant->update(['status' => 'approved']);
    $this->get(route('activities.index', ['status' => 'available']))->assertDontSee($activity->title);
    $this->get(route('activities.index', ['status' => 'full']))->assertSee($activity->title);
    $this->get(route('activities.index', ['status' => 'full', 'location' => 'ห้องสมุด', 'q' => 'บาส']))->assertDontSee($activity->title);
    $activity->update(['starts_at' => now()->subHour()]);
    $this->get(route('activities.index', ['status' => 'full']))->assertDontSee($activity->title);
    $this->get(route('activities.index', ['status' => 'all']))->assertSee($activity->title);
    $activity->status = 'cancelled';
    $activity->save();
    $this->get(route('activities.index', ['status' => 'all']))->assertSee($activity->title);
});

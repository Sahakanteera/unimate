<?php

use App\Models\Activity;
use App\Models\Category;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
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
        ->assertSee('ลิงก์สถานที่ใน Google Maps')
        ->assertSee('id="delete-location-image-dialog"', false)
        ->assertSee('ยืนยันลบรูป')->assertDontSee('name="remove_location_image"', false);
    $this->put(route('activities.update', $activity), [
        ...$this->payload,
        'location_image' => UploadedFile::fake()->image('new-court.png'),
        'google_maps_url' => 'https://www.google.com/maps/place/Bangkok/',
    ])->assertRedirect();
    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($activity->fresh()->location_image_path);
    $newPath = $activity->fresh()->location_image_path;
    expect($activity->fresh()->google_maps_url)->toBe('https://www.google.com/maps/place/Bangkok/');
    $this->deleteJson(route('activities.location-image.destroy', $activity))->assertOk();
    Storage::disk('public')->assertMissing($newPath);
    expect($activity->fresh()->location_image_path)->toBeNull();
    expect($activity->fresh()->google_maps_url)->toBe('https://www.google.com/maps/place/Bangkok/');
    expect($activity->fresh()->title)->toBe($this->payload['title']);
    $this->deleteJson(route('activities.location-image.destroy', $activity))->assertOk();
    $this->put(route('activities.update', $activity), [...$this->payload, 'google_maps_url' => ''])->assertRedirect();
    expect($activity->fresh()->google_maps_url)->toBeNull();
    $this->get(route('activities.show', $activity))->assertDontSee('เปิดสถานที่ใน Google Maps');
});

test('failed location photo storage does not create an activity', function () {
    $disk = Storage::disk('public');
    $failingDisk = Mockery::mock($disk);
    $failingDisk->shouldReceive('putFileAs')->once()->andReturnFalse();
    Storage::set('public', $failingDisk);

    $this->actingAs($this->owner)->postJson(route('activities.store'), [
        ...$this->payload,
        'location_image' => UploadedFile::fake()->image('court.jpg'),
    ])->assertStatus(503);

    expect(Activity::count())->toBe(0);
    expect($disk->allFiles())->toBe([]);
});

test('failed replacement photo storage keeps the activity and old photo', function () {
    $this->actingAs($this->owner)->post(route('activities.store'), [
        ...$this->payload,
        'location_image' => UploadedFile::fake()->image('court.jpg'),
        'google_maps_url' => 'https://maps.app.goo.gl/original',
    ])->assertRedirect();
    $activity = Activity::firstOrFail();
    $oldPath = $activity->location_image_path;
    $disk = Storage::disk('public');
    $failingDisk = Mockery::mock($disk);
    $failingDisk->shouldReceive('putFileAs')->once()->andReturnFalse();
    Storage::set('public', $failingDisk);

    $this->putJson(route('activities.update', $activity), [
        ...$this->payload,
        'title' => 'ชื่อใหม่ที่ยังไม่ควรบันทึก',
        'location_image' => UploadedFile::fake()->image('replacement.jpg'),
        'google_maps_url' => 'https://maps.app.goo.gl/replacement',
    ])->assertStatus(503);

    expect($activity->fresh()->title)->toBe($this->payload['title']);
    expect($activity->fresh()->location_image_path)->toBe($oldPath);
    expect($activity->fresh()->google_maps_url)->toBe('https://maps.app.goo.gl/original');
    $disk->assertExists($oldPath);
    expect($disk->allFiles())->toBe([$oldPath]);
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
        ...$this->payload, 'google_maps_url' => 'https://maps.app.goo.gl/other',
    ])->assertForbidden();
    $this->deleteJson(route('activities.location-image.destroy', $activity))->assertForbidden();
    expect($activity->fresh()->location_image_path)->toBe($path);
    expect($activity->fresh()->google_maps_url)->toBe('https://maps.app.goo.gl/example');
    Storage::disk('public')->assertExists($path);
});

test('admin cannot delete another owners photo', function () {
    $this->actingAs($this->owner)->post(route('activities.store'), [
        ...$this->payload, 'location_image' => UploadedFile::fake()->image('court.jpg'),
    ])->assertRedirect();
    $activity = Activity::firstOrFail();
    $path = $activity->location_image_path;
    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->deleteJson(route('activities.location-image.destroy', $activity))->assertForbidden();
    expect($activity->fresh()->location_image_path)->toBe($path);
    Storage::disk('public')->assertExists($path);
});

test('cancelled activity keeps its photo when deletion is requested', function () {
    $this->actingAs($this->owner)->post(route('activities.store'), [
        ...$this->payload, 'location_image' => UploadedFile::fake()->image('court.jpg'),
    ])->assertRedirect();
    $activity = Activity::firstOrFail();
    $path = $activity->location_image_path;
    $activity->forceFill(['status' => 'cancelled'])->save();
    $this->deleteJson(route('activities.location-image.destroy', $activity))->assertStatus(409);
    expect($activity->fresh()->location_image_path)->toBe($path);
    Storage::disk('public')->assertExists($path);
});

test('owner can immediately delete a photo after the activity has started', function () {
    $this->actingAs($this->owner)->post(route('activities.store'), [
        ...$this->payload, 'location_image' => UploadedFile::fake()->image('court.jpg'),
    ])->assertRedirect();
    $activity = Activity::firstOrFail();
    $path = $activity->location_image_path;
    $activity->update(['starts_at' => now()->subHour()]);
    $start = $activity->fresh()->starts_at;
    $this->deleteJson(route('activities.location-image.destroy', $activity))->assertOk();
    expect($activity->fresh()->location_image_path)->toBeNull();
    expect($activity->fresh()->starts_at->equalTo($start))->toBeTrue();
    Storage::disk('public')->assertMissing($path);
});

test('photo stays attached when storage cannot delete the file', function () {
    $this->actingAs($this->owner)->post(route('activities.store'), [
        ...$this->payload, 'location_image' => UploadedFile::fake()->image('court.jpg'),
    ])->assertRedirect();
    $activity = Activity::firstOrFail();
    $path = $activity->location_image_path;
    $disk = Storage::disk('public');
    $failingDisk = Mockery::mock(FilesystemAdapter::class);
    $failingDisk->shouldReceive('exists')->once()->with($path)->andReturnTrue();
    $failingDisk->shouldReceive('delete')->once()->with($path)->andReturnFalse();
    Storage::shouldReceive('disk')->with('public')->andReturn($failingDisk);
    $this->deleteJson(route('activities.location-image.destroy', $activity))->assertStatus(503);
    expect($activity->fresh()->location_image_path)->toBe($path);
    $disk->assertExists($path);
});

test('guest cannot delete an activity photo', function () {
    $this->actingAs($this->owner)->post(route('activities.store'), [
        ...$this->payload, 'location_image' => UploadedFile::fake()->image('court.jpg'),
    ])->assertRedirect();
    $activity = Activity::firstOrFail();
    $path = $activity->location_image_path;
    auth()->logout();
    $this->deleteJson(route('activities.location-image.destroy', $activity))->assertUnauthorized();
    expect($activity->fresh()->location_image_path)->toBe($path);
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
    $payload = Arr::except($this->payload, ['starts_at', 'ends_at']);
    $this->actingAs($this->owner)->post(route('activities.store'), [
        ...$payload, 'starts_at_date' => $date, 'starts_at_time' => '14:30',
        'ends_at_date' => $date, 'ends_at_time' => '16:00',
    ])->assertRedirect();
    $activity = Activity::firstOrFail();
    expect($activity->starts_at->format('Y-m-d H:i'))->toBe($date.' 14:30');
    expect($activity->ends_at->format('Y-m-d H:i'))->toBe($date.' 16:00');
    expect($activity->starts_at->timezoneName)->toBe('Asia/Bangkok');
    $this->get(route('activities.edit', $activity))->assertSee('id="starts_at_time"', false)
        ->assertSee('value="14:30"', false)->assertDontSee('datetime-local');
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
    $this->travelTo(Carbon::parse('2026-10-10 23:30:00', 'Asia/Bangkok'));
    $this->actingAs($this->owner)->get(route('activities.create'))->assertOk()
        ->assertSee('value="11/10/2026"', false)
        ->assertSee('value="00:30"', false)->assertSee('value="01:30"', false)->assertDontSee('datetime-local');
});

test('day month year calendar and time range persist correct Bangkok datetime', function () {
    $year = (string) (now()->year + 1);
    $payload = Arr::except($this->payload, ['starts_at', 'ends_at']);
    $this->actingAs($this->owner)->post(route('activities.store'), [
        ...$payload, 'starts_at_date' => '13/04/'.$year, 'starts_at_time' => '22:33',
        'ends_at_date' => '14/04/'.$year, 'ends_at_time' => '00:15',
    ])->assertSessionHasNoErrors()->assertRedirect();
    $activity = Activity::firstOrFail();
    expect($activity->starts_at->format('Y-m-d H:i'))->toBe($year.'-04-13 22:33');
    expect($activity->ends_at->format('Y-m-d H:i'))->toBe($year.'-04-14 00:15');
    $this->get(route('activities.edit', $activity))->assertOk()->assertSeeInOrder([
        'id="starts_at_date"', 'id="starts_at_time"', 'id="ends_at_date"', 'id="ends_at_time"',
    ], false);
    $this->get(route('activities.edit', $activity))->assertSee('value="13/04/'.$year.'"', false)
        ->assertSee('value="22:33"', false);
});

test('separate hour and minute inputs save every minute and preserve leading zeros', function () {
    $date = now()->addDays(2)->format('d/m/Y');
    $payload = Arr::except($this->payload, ['starts_at', 'ends_at']);
    $this->actingAs($this->owner)->post(route('activities.store'), [
        ...$payload, 'starts_at_date' => $date, 'starts_at_time_hour' => '15', 'starts_at_time_minute' => '48',
        'ends_at_date' => $date, 'ends_at_time_hour' => '16', 'ends_at_time_minute' => '7',
    ])->assertSessionHasNoErrors()->assertRedirect();
    $activity = Activity::firstOrFail();
    expect($activity->starts_at->format('H:i'))->toBe('15:48');
    expect($activity->ends_at->format('H:i'))->toBe('16:07');
    $this->get(route('activities.index', ['time_from_hour' => '15', 'time_from_minute' => '48', 'time_to_hour' => '15', 'time_to_minute' => '48']))
        ->assertOk()->assertSee($activity->title);
    $this->get(route('activities.index', ['time_from_hour' => '', 'time_from_minute' => '', 'time_to_hour' => '', 'time_to_minute' => '']))
        ->assertOk()->assertSee($activity->title);
    $this->get(route('activities.index', ['time_from_hour' => '15', 'time_from_minute' => '']))->assertSessionHasErrors('time_from');
});

test('separate clock inputs reject invalid or incomplete times', function (mixed $hour, mixed $minute) {
    $this->actingAs($this->owner)->post(route('activities.store'), [
        ...$this->payload, 'starts_at_date' => now()->addDays(2)->format('d/m/Y'),
        'starts_at_time_hour' => $hour, 'starts_at_time_minute' => $minute,
    ])->assertSessionHasErrors('starts_at_time');
    expect(Activity::count())->toBe(0);
})->with([['24', '00'], ['15', '60'], ['-1', '30'], ['15', ''], [['15'], '30']]);

test('calendar and time range reject impossible dates and invalid time values', function (array $changes, string $error) {
    $payload = Arr::except($this->payload, ['starts_at', 'ends_at']);
    $year = (string) (now()->year + 1);
    $this->actingAs($this->owner)->post(route('activities.store'), [
        ...$payload, 'starts_at_date' => '13/04/'.$year, 'starts_at_time' => '14:30',
        'ends_at_date' => '13/04/'.$year, 'ends_at_time' => '16:00', ...$changes,
    ])->assertSessionHasErrors($error);
    expect(Activity::count())->toBe(0);
})->with([
    [['starts_at_date' => '31/04/2099'], 'starts_at_date'],
    [['starts_at_date' => '13/13/2099'], 'starts_at_date'],
    [['starts_at_time' => '24:00'], 'starts_at_time'],
    [['starts_at_time' => '14:60'], 'starts_at_time'],
    [['starts_at_time' => ''], 'starts_at_time'],
    [['ends_at_time' => '13:00'], 'ends_at'],
]);

test('activity search accepts day month year dates and a start time range', function () {
    $date = now()->addDays(2)->format('Y-m-d');
    $this->actingAs($this->owner)->post(route('activities.store'), [
        ...$this->payload, 'starts_at' => $date.'T14:30', 'ends_at' => $date.'T16:00',
    ])->assertSessionHasNoErrors()->assertRedirect();
    $filters = ['date' => now()->addDays(2)->format('d/m/Y'), 'time_from' => '14:00', 'time_to' => '15:00'];
    $this->get(route('activities.index', $filters))->assertOk()->assertSee($this->payload['title']);
    $this->get(route('activities.index', [...$filters, 'time_from' => '14:30', 'time_to' => '14:30']))->assertSee($this->payload['title']);
    $this->get(route('activities.index', [...$filters, 'time_from' => '16:00', 'time_to' => '17:00']))->assertDontSee($this->payload['title']);
    $this->get(route('activities.index', [...$filters, 'date' => '31/04/2099']))->assertSessionHasErrors('date');
    $this->get(route('activities.index', [...$filters, 'time_from' => '23:00', 'time_to' => '15:00']))->assertSee($this->payload['title']);
    $this->get(route('activities.index', [...$filters, 'time_from' => '24:00']))->assertSessionHasErrors('time_from');
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

<?php

use App\Models\Activity;
use App\Models\Category;
use App\Models\User;

beforeEach(function () {
    $this->owner = User::factory()->create();
    $this->category = Category::create(['name' => 'กีฬา']);
    $this->payload = [
        'title' => 'ชวนเล่นแบดมินตัน',
        'description' => 'หาเพื่อนเล่นหลังเลิกเรียน',
        'category_id' => $this->category->id,
        'location' => 'สนามกีฬา',
        'starts_at' => now()->addDays(2)->format('Y-m-d\TH:i'),
        'ends_at' => now()->addDays(2)->addHours(2)->format('Y-m-d\TH:i'),
        'capacity' => 4,
    ];
    $this->activity = new Activity($this->payload);
    $this->activity->user_id = $this->owner->id;
    $this->activity->save();
});

test('owner can publish search view edit and cancel an activity', function () {
    $this->actingAs($this->owner)->get(route('activities.create'))->assertOk();
    $this->post(route('activities.store'), [...$this->payload, 'title' => 'กิจกรรมใหม่', 'user_id' => 999, 'status' => 'cancelled'])->assertRedirect();
    $created = Activity::where('title', 'กิจกรรมใหม่')->firstOrFail();
    expect($created->user_id)->toBe($this->owner->id);
    expect($created->status)->toBe('published');
    $this->get(route('activities.index', ['q' => 'แบด', 'category_id' => $this->category->id, 'location' => 'สนาม', 'date' => now()->addDays(2)->format('Y-m-d')]))
        ->assertOk()->assertSee('ชวนเล่นแบดมินตัน')->assertDontSee('กิจกรรมใหม่');
    $this->get(route('activities.show', $this->activity))->assertOk()->assertSee('แก้ไขกิจกรรม');
    $this->get(route('activities.edit', $this->activity))->assertOk();
    $this->put(route('activities.update', $this->activity), [...$this->payload, 'location' => 'สนามใหม่'])->assertRedirect();
    expect($this->activity->fresh()->location)->toBe('สนามใหม่');
    $this->patch(route('activities.cancel', $this->activity))->assertRedirect();
    expect($this->activity->fresh()->status)->toBe('cancelled');
    $this->get(route('activities.index'))->assertDontSee('ชวนเล่นแบดมินตัน');
    $this->get(route('activities.index', ['status' => 'cancelled']))->assertSee('ชวนเล่นแบดมินตัน');
    $this->get(route('activities.show', $this->activity))->assertSee('กิจกรรมนี้ถูกยกเลิกแล้ว');
    $this->get(route('activities.edit', $this->activity))->assertStatus(409);
    $this->put(route('activities.update', $this->activity), $this->payload)->assertStatus(409);
});

test('other users including admins cannot edit or cancel another owners post', function (string $role) {
    $other = User::factory()->create(['role' => $role]);
    $this->actingAs($other)->get(route('activities.show', $this->activity))->assertOk()->assertDontSee(route('activities.edit', $this->activity));
    $this->get(route('activities.edit', $this->activity))->assertForbidden();
    $this->put(route('activities.update', $this->activity), [...$this->payload, 'title' => 'แอบแก้ไข'])->assertForbidden();
    $this->patch(route('activities.cancel', $this->activity))->assertForbidden();
    expect($this->activity->fresh()->title)->toBe($this->payload['title']);
    expect($this->activity->fresh()->status)->toBe('published');
})->with(['student', 'admin']);

test('invalid activity fields are rejected', function (string $field, mixed $value) {
    $this->actingAs($this->owner)->post(route('activities.store'), [...$this->payload, $field => $value])->assertSessionHasErrors($field);
    expect(Activity::count())->toBe(1);
})->with([
    ['title', ''], ['category_id', 999], ['capacity', 0], ['capacity', 1.5], ['capacity', 10001],
    ['starts_at', '2000-01-01T10:00'], ['ends_at', '2000-01-01T10:00'], ['location', ''],
]);

test('all filters narrow results and description search cannot bypass other filters', function () {
    $this->actingAs($this->owner);
    foreach ([['q' => 'ไม่มีคำนี้'], ['location' => 'ห้องสมุด'], ['date' => '2000-01-01'], ['status' => 'cancelled']] as $filters) {
        $this->get(route('activities.index', $filters))->assertOk()->assertDontSee($this->activity->title);
    }
    $otherCategory = Category::create(['name' => 'ดนตรี']);
    $this->get(route('activities.index', ['q' => 'เลิกเรียน', 'category_id' => $otherCategory->id]))->assertDontSee($this->activity->title);
    $this->get(route('activities.index', ['q' => 'เลิกเรียน']))->assertSee($this->activity->title);
});

test('guests and suspended users cannot publish', function () {
    $this->get(route('activities.index'))->assertRedirect(route('login'));
    $this->post(route('activities.store'), $this->payload)->assertRedirect(route('login'));
    $suspended = User::factory()->create(['status' => 'suspended']);
    $this->actingAs($suspended)->post(route('activities.store'), $this->payload)->assertRedirect(route('login'));
    expect(Activity::count())->toBe(1);
});

test('only admins can manage categories and used categories cannot be deleted', function () {
    $this->actingAs($this->owner)->get(route('admin.categories.index'))->assertForbidden();
    $this->post(route('admin.categories.store'), ['name' => 'แอบเพิ่ม'])->assertForbidden();
    $this->put(route('admin.categories.update', $this->category), ['name' => 'แอบแก้'])->assertForbidden();
    $this->delete(route('admin.categories.destroy', $this->category))->assertForbidden();
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $this->get(route('admin.categories.index'))->assertOk();
    $this->post(route('admin.categories.store'), ['name' => 'ท่องเที่ยว'])->assertRedirect();
    $category = Category::where('name', 'ท่องเที่ยว')->firstOrFail();
    $this->put(route('admin.categories.update', $category), ['name' => 'กีฬา'])->assertSessionHasErrors('name');
    $this->put(route('admin.categories.update', $category), ['name' => 'เดินป่า'])->assertRedirect();
    expect($category->fresh()->name)->toBe('เดินป่า');
    $this->delete(route('admin.categories.destroy', $this->category))->assertSessionHas('error');
    expect($this->category->fresh())->not->toBeNull();
    $this->delete(route('admin.categories.destroy', $category))->assertRedirect();
    expect($category->fresh())->toBeNull();
});

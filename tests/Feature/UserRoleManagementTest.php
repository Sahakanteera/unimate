<?php

use App\Models\User;

test('admin can promote a student to admin', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    $student = User::factory()->create(['role' => 'student', 'status' => 'active']);

    $this->actingAs($admin)
        ->post(route('admin.users.toggle-role', $student))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($student->fresh()->role)->toBe('admin');
});

test('admin can demote another admin to student', function () {
    $admin1 = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    $admin2 = User::factory()->create(['role' => 'admin', 'status' => 'active']);

    $this->actingAs($admin1)
        ->post(route('admin.users.toggle-role', $admin2))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($admin2->fresh()->role)->toBe('student');
});

test('admin cannot change their own role', function () {
    $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

    $this->actingAs($admin)
        ->post(route('admin.users.toggle-role', $admin))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($admin->fresh()->role)->toBe('admin');
});

test('student cannot access role toggle route', function () {
    $student1 = User::factory()->create(['role' => 'student', 'status' => 'active']);
    $student2 = User::factory()->create(['role' => 'student', 'status' => 'active']);

    $this->actingAs($student1)
        ->post(route('admin.users.toggle-role', $student2))
        ->assertForbidden();

    expect($student2->fresh()->role)->toBe('student');
});

test('guest is redirected to login when trying to toggle role', function () {
    $student = User::factory()->create(['role' => 'student', 'status' => 'active']);

    $this->post(route('admin.users.toggle-role', $student))
        ->assertRedirect(route('login'));

    expect($student->fresh()->role)->toBe('student');
});

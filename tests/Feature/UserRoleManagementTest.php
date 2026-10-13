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

test('user action confirmations keep quoted names out of executable JavaScript', function (string $name) {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['name' => $name, 'role' => 'student']);
    $response = $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
    $document = new DOMDocument;
    $document->loadHTML($response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
    $xpath = new DOMXPath($document);

    foreach (['admin.users.toggle-role', 'admin.users.toggle-status'] as $action) {
        $form = $xpath->query('//form[@action="'.route($action, $user).'"]')->item(0);
        expect($form)->toBeInstanceOf(DOMElement::class);
        expect($form->getAttribute('onsubmit'))->toContain('confirm(')->not->toContain($name);
        expect($form->getAttribute('data-confirm'))->toContain($name);
    }
})->with(["O'Brien", "'+(globalThis.confirmationProbe=true)+'"]);

test('a stale role form preserves the role that the admin confirmed', function (string $initialRole, string $targetRole) {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => $initialRole]);
    $response = $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
    $document = new DOMDocument;
    $document->loadHTML($response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
    $xpath = new DOMXPath($document);
    $form = $xpath->query('//form[@action="'.route('admin.users.toggle-role', $user).'"]')->item(0);
    expect($form)->toBeInstanceOf(DOMElement::class);
    $payload = [];
    foreach ($xpath->query('.//input[@name]', $form) as $input) {
        $payload[$input->getAttribute('name')] = $input->getAttribute('value');
    }

    $user->update(['role' => $targetRole]);
    $this->post(route('admin.users.toggle-role', $user), $payload)->assertRedirect();

    expect($user->fresh()->role)->toBe($targetRole);
})->with([
    'promote after another admin already promoted' => ['student', 'admin'],
    'demote after another admin already demoted' => ['admin', 'student'],
]);

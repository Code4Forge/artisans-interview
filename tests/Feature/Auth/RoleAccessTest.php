<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('sends guests from the dashboard to the login page', function () {
    $this->get(route('admin.dashboard.index'))->assertRedirect(route('login'));
});

it('shows the role home on the admin dashboard', function (string $role, string $label) {
    $user = User::factory()->withRole($role)->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard.index'))
        ->assertOk()
        ->assertSee($label)
        ->assertSee($user->roleSummary());
})->with([
    'admin' => ['admin', 'Admin'],
    'teacher' => ['teacher', 'Teacher'],
    'student' => ['student', 'Student'],
    'parent' => ['parent', 'Parent'],
]);

it('forbids an account without dashboard permission', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard.index'))
        ->assertForbidden();
});

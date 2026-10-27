<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('signs in an admin and sends them toward their home', function () {
    $admin = User::factory()->withRole('admin')->create();

    $this->post(route('login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard.index'));

    $this->assertAuthenticatedAs($admin);
});

it('rejects a wrong password', function () {
    $user = User::factory()->withRole('teacher')->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'not-the-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('rejects an empty login', function () {
    $this->post(route('login.store'), [
        'email' => '',
        'password' => '',
    ])->assertSessionHasErrors(['email', 'password']);

    $this->assertGuest();
});

it('returns 429 after repeated failed logins', function () {
    $user = User::factory()->withRole('teacher')->create();

    foreach (range(1, 5) as $attempt) {
        $this->postJson(route('login.store'), [
            'email' => $user->email,
            'password' => 'not-the-password',
        ])->assertUnprocessable();
    }

    $this->postJson(route('login.store'), [
        'email' => $user->email,
        'password' => 'not-the-password',
    ])->assertTooManyRequests();

    $this->assertGuest();
});

it('logs the user out', function () {
    $user = User::factory()->withRole('parent')->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

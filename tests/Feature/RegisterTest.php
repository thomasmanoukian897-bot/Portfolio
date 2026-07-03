<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('register page can be rendered', function () {
    $this->get(route('register'))
        ->assertSuccessful()
        ->assertSee('Create Your')
        ->assertSee('Create Account');
});

test('new users can register', function () {
    $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('home'));

    $this->assertAuthenticated();

    $this->assertDatabaseHas('users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => UserRole::User->value,
    ]);
});

test('registration requires a unique email', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $this->post(route('register'), [
        'name' => 'Another User',
        'email' => 'existing@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('registration requires password confirmation', function () {
    $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'wrong-password',
    ])->assertSessionHasErrors('password');

    $this->assertGuest();
});

test('authenticated users cannot access the register page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('register'))
        ->assertRedirect(route('home'));
});

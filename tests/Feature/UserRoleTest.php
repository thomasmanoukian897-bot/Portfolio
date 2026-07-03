<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function () {
    Route::middleware(['auth', 'role:admin'])->get('/admin-only', fn () => 'admin area');
});

test('users default to the user role', function () {
    $user = User::factory()->create();

    expect($user->role)->toBe(UserRole::User)
        ->and($user->hasRole(UserRole::User))->toBeTrue()
        ->and($user->hasRole('user'))->toBeTrue()
        ->and($user->isAdmin())->toBeFalse();
});

test('admin factory state assigns the admin role', function () {
    $user = User::factory()->admin()->create();

    expect($user->role)->toBe(UserRole::Admin)
        ->and($user->hasRole(UserRole::Admin))->toBeTrue()
        ->and($user->hasRole('admin'))->toBeTrue()
        ->and($user->isAdmin())->toBeTrue();
});

test('admins can access role protected routes', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin-only')
        ->assertSuccessful()
        ->assertSee('admin area');
});

test('regular users cannot access role protected routes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin-only')
        ->assertForbidden();
});

test('guests cannot access role protected routes', function () {
    $this->get('/admin-only')
        ->assertRedirect(route('login'));
});

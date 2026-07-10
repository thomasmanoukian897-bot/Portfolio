<?php

use App\Mail\PasswordChangeVerificationCode;
use App\Models\PasswordChangeVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('guests cannot access the profile page', function () {
    $this->get(route('profile.edit'))
        ->assertRedirect(route('login'));
});

test('authenticated users can view their profile page', function () {
    $user = User::factory()->create(['name' => 'Jane Doe']);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertSuccessful()
        ->assertSee('Profile')
        ->assertSee('Jane Doe')
        ->assertSee('Display Name')
        ->assertSee('Handle');
});

test('authenticated users can update their display name', function () {
    $user = User::factory()->create(['name' => 'Old Name']);

    $this->actingAs($user)
        ->put(route('profile.update'), [
            'name' => 'New Display Name',
            'handle' => $user->handle,
        ])
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHas('status');

    expect($user->fresh()->name)->toBe('New Display Name');
});

test('authenticated users can upload an avatar', function () {
    Storage::fake('public');

    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('profile.update'), [
            'name' => $user->name,
            'handle' => $user->handle,
            'avatar' => UploadedFile::fake()->image('avatar.jpg'),
        ])
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->avatar_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar_path);
});

test('replacing an avatar deletes the old file', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $oldPath = UploadedFile::fake()->image('old.jpg')->store('avatars', 'public');

    $user->update(['avatar_path' => $oldPath]);

    $this->actingAs($user)
        ->put(route('profile.update'), [
            'name' => $user->name,
            'handle' => $user->handle,
            'avatar' => UploadedFile::fake()->image('new.jpg'),
        ])
        ->assertRedirect(route('profile.edit'));

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($user->fresh()->avatar_path);
});

test('profile update requires a valid display name', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('profile.update'), [
            'name' => '',
            'handle' => $user->handle,
        ])
        ->assertSessionHasErrors('name');
});

test('authenticated users can update their handle', function () {
    $user = User::factory()->create(['name' => 'Jane Doe']);

    $this->actingAs($user)
        ->put(route('profile.update'), [
            'name' => 'Jane Doe',
            'handle' => 'jane-writes',
        ])
        ->assertRedirect(route('profile.edit'))
        ->assertSessionHas('status');

    expect($user->fresh()->handle)->toBe('jane-writes');
});

test('profile update requires a unique handle', function () {
    $user = User::factory()->create(['name' => 'Jane Doe']);
    User::factory()->create(['name' => 'Other User', 'handle' => 'taken-handle']);

    $this->actingAs($user)
        ->put(route('profile.update'), [
            'name' => 'Jane Doe',
            'handle' => 'taken-handle',
        ])
        ->assertSessionHasErrors('handle');
});

test('profile update requires a valid handle format', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('profile.update'), [
            'name' => $user->name,
            'handle' => '!!!',
        ])
        ->assertSessionHasErrors('handle');
});

test('navigation shows profile dropdown for authenticated users', function () {
    $user = User::factory()->create(['name' => 'Alex Smith']);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Profile')
        ->assertSee('Alex Smith')
        ->assertSee('Edit Profile');
});

test('profile page shows change password tab', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile.edit', ['tab' => 'password']))
        ->assertSuccessful()
        ->assertSee('Change Password')
        ->assertSee('Current Password')
        ->assertSee('Send Verification Code');
});

test('requesting a password change sends a verification email', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('profile.password.request'), [
            'current_password' => 'password',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])
        ->assertRedirect(route('profile.edit', ['tab' => 'password']))
        ->assertSessionHas('password_status');

    expect(PasswordChangeVerification::findActiveForUser($user))->not->toBeNull();

    Mail::assertSent(PasswordChangeVerificationCode::class, function (PasswordChangeVerificationCode $mail) use ($user) {
        return $mail->hasTo($user->email)
            && $mail->user->is($user)
            && strlen($mail->code) === 6;
    });
});

test('password change requires the correct current password', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('profile.password.request'), [
            'current_password' => 'wrong-password',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])
        ->assertSessionHasErrors('current_password');

    Mail::assertNothingSent();
});

test('users can verify a password change with the emailed code', function () {
    Mail::fake();

    $user = User::factory()->create();
    $capturedCode = null;

    $this->actingAs($user)
        ->post(route('profile.password.request'), [
            'current_password' => 'password',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ]);

    Mail::assertSent(PasswordChangeVerificationCode::class, function (PasswordChangeVerificationCode $mail) use (&$capturedCode) {
        $capturedCode = $mail->code;

        return true;
    });

    $this->actingAs($user)
        ->post(route('profile.password.verify'), [
            'code' => $capturedCode,
        ])
        ->assertRedirect(route('profile.edit', ['tab' => 'password']))
        ->assertSessionHas('password_status');

    expect(Hash::check('new-secure-password', $user->fresh()->password))->toBeTrue();
    expect(PasswordChangeVerification::findActiveForUser($user))->toBeNull();
});

test('password change verification rejects invalid codes', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('profile.password.request'), [
            'current_password' => 'password',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ]);

    $this->actingAs($user)
        ->post(route('profile.password.verify'), [
            'code' => '000000',
        ])
        ->assertSessionHasErrors('code');

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});

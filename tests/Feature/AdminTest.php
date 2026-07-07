<?php

use App\Contracts\GoogleCalendarService;
use App\Enums\UserRole;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'reservations.timezone' => 'UTC',
        'reservations.start_hour' => 9,
        'reservations.end_hour' => 17,
        'reservations.duration_minutes' => 60,
        'reservations.advance_days' => 30,
        'reservations.slot_interval_minutes' => 60,
    ]);

    Carbon::setTestNow(Carbon::parse('2026-07-07 08:00:00', 'UTC'));

    $this->mock(GoogleCalendarService::class, function ($mock) {
        $mock->shouldReceive('deleteEvent')->andReturnNull();
        $mock->shouldReceive('updateEvent')->andReturnNull();
        $mock->shouldReceive('getBusyPeriods')->andReturn(collect());
    });
});

afterEach(function () {
    Carbon::setTestNow();
});

test('guests cannot access admin dashboard', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

test('regular users cannot access admin dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('admins can access admin dashboard', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertSuccessful()
        ->assertSee('Total Users')
        ->assertSee('Recent Users');
});

test('admins can list users', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['name' => 'Jane Doe']);

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertSuccessful()
        ->assertSee('Jane Doe');
});

test('admins can update a user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create(['name' => 'Jane Doe']);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $user), [
            'name' => 'Jane Smith',
            'email' => $user->email,
            'role' => UserRole::Admin->value,
        ])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('status');

    expect($user->fresh())
        ->name->toBe('Jane Smith')
        ->role->toBe(UserRole::Admin);
});

test('admins cannot delete themselves', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $admin))
        ->assertForbidden();

    expect($admin->fresh())->not->toBeNull();
});

test('admins can delete other users', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.users.destroy', $user))
        ->assertRedirect(route('admin.users.index'));

    expect(User::find($user->id))->toBeNull();
});

test('admins are redirected to admin dashboard after login', function () {
    $admin = User::factory()->admin()->create();

    $this->post(route('login'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($admin);
});

test('regular users cannot access admin bookings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.bookings.index'))
        ->assertForbidden();
});

test('admins can list bookings', function () {
    $admin = User::factory()->admin()->create();
    $booking = Reservation::factory()->create(['name' => 'Jane Doe']);

    $this->actingAs($admin)
        ->get(route('admin.bookings.index'))
        ->assertSuccessful()
        ->assertSee('Jane Doe')
        ->assertSee('Bookings');
});

test('admins can cancel bookings', function () {
    $admin = User::factory()->admin()->create();
    $booking = Reservation::factory()->create(['name' => 'Jane Doe']);

    $this->actingAs($admin)
        ->delete(route('admin.bookings.destroy', $booking))
        ->assertRedirect(route('admin.bookings.index'))
        ->assertSessionHas('status');

    expect(Reservation::find($booking->id))->toBeNull();
});

test('admins can edit bookings', function () {
    $admin = User::factory()->admin()->create();
    $booking = Reservation::factory()->create([
        'name' => 'Jane Doe',
        'starts_at' => Carbon::parse('2026-07-08 10:00:00', 'UTC'),
        'ends_at' => Carbon::parse('2026-07-08 11:00:00', 'UTC'),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.bookings.edit', $booking))
        ->assertSuccessful()
        ->assertSee('Jane Doe')
        ->assertSee('10:00 AM');
});

test('admins can update booking time', function () {
    $admin = User::factory()->admin()->create();
    $booking = Reservation::factory()->create([
        'starts_at' => Carbon::parse('2026-07-08 10:00:00', 'UTC'),
        'ends_at' => Carbon::parse('2026-07-08 11:00:00', 'UTC'),
    ]);
    $newStartsAt = Carbon::parse('2026-07-08 14:00:00', 'UTC');

    $this->actingAs($admin)
        ->put(route('admin.bookings.update', $booking), [
            'starts_at' => $newStartsAt->toIso8601String(),
        ])
        ->assertRedirect(route('admin.bookings.index'))
        ->assertSessionHas('status');

    $booking->refresh();

    expect($booking->starts_at->timezone('UTC')->format('Y-m-d H:i:s'))->toBe('2026-07-08 14:00:00');
    expect($booking->ends_at->timezone('UTC')->format('Y-m-d H:i:s'))->toBe('2026-07-08 15:00:00');
});

test('admins cannot update booking to an already reserved slot', function () {
    $admin = User::factory()->admin()->create();
    $booking = Reservation::factory()->create([
        'starts_at' => Carbon::parse('2026-07-08 10:00:00', 'UTC'),
        'ends_at' => Carbon::parse('2026-07-08 11:00:00', 'UTC'),
    ]);

    Reservation::factory()->create([
        'starts_at' => Carbon::parse('2026-07-08 14:00:00', 'UTC'),
        'ends_at' => Carbon::parse('2026-07-08 15:00:00', 'UTC'),
    ]);

    $this->actingAs($admin)
        ->put(route('admin.bookings.update', $booking), [
            'starts_at' => Carbon::parse('2026-07-08 14:00:00', 'UTC')->toIso8601String(),
        ])
        ->assertSessionHasErrors('starts_at');

    expect($booking->fresh()->starts_at->timezone('UTC')->format('H:i'))->toBe('10:00');
});

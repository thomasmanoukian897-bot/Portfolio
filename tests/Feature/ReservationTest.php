<?php

use App\Contracts\GoogleCalendarService;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'app.timezone' => 'UTC',
        'reservations.timezone' => 'UTC',
        'reservations.start_hour' => 9,
        'reservations.end_hour' => 17,
        'reservations.duration_minutes' => 60,
        'reservations.advance_days' => 30,
        'reservations.slot_interval_minutes' => 60,
    ]);

    Carbon::setTestNow(Carbon::parse('2026-07-07 08:00:00', 'UTC'));

    $this->mock(GoogleCalendarService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->andReturn(false);
        $mock->shouldReceive('getBusyPeriods')->andReturn(collect());
        $mock->shouldReceive('createEvent')->andReturn('google-event-123');
    });
});

afterEach(function () {
    Carbon::setTestNow();
});

test('reservation page can be rendered', function () {
    $this->get(route('reservations.index'))
        ->assertSuccessful()
        ->assertSee('Book a Session')
        ->assertSee('Select a Date');
});

test('reservation page shows available time slots for a weekday', function () {
    $this->get(route('reservations.index', ['date' => '2026-07-08']))
        ->assertSuccessful()
        ->assertSee('9:00 AM')
        ->assertSee('10:00 AM')
        ->assertSee('4:00 PM');
});

test('reservation page shows no slots on weekends', function () {
    $this->get(route('reservations.index', ['date' => '2026-07-11']))
        ->assertSuccessful()
        ->assertSee('No available time slots');
});

test('guests can create a reservation', function () {
    $startsAt = Carbon::parse('2026-07-08 10:00:00', 'UTC');

    $this->post(route('reservations.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'starts_at' => $startsAt->toIso8601String(),
        'notes' => 'Looking forward to it.',
    ])
        ->assertRedirect(route('reservations.index', ['date' => '2026-07-08']))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('reservations', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'user_id' => null,
    ]);

    $reservation = Reservation::query()->first();

    expect($reservation->starts_at->timezone(config('reservations.timezone'))->format('Y-m-d H:i:s'))->toBe('2026-07-08 10:00:00');
    expect($reservation->ends_at->timezone(config('reservations.timezone'))->format('Y-m-d H:i:s'))->toBe('2026-07-08 11:00:00');
});

test('authenticated users have reservations linked to their account', function () {
    $user = User::factory()->create();
    $startsAt = Carbon::parse('2026-07-08 11:00:00', 'UTC');

    $this->actingAs($user)
        ->post(route('reservations.store'), [
            'name' => $user->name,
            'email' => $user->email,
            'starts_at' => $startsAt->toIso8601String(),
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('reservations', [
        'user_id' => $user->id,
        'email' => $user->email,
    ]);
});

test('reservation page shows past time slots as unavailable', function () {
    Carbon::setTestNow(Carbon::parse('2026-07-07 11:00:00', 'UTC'));

    $this->get(route('reservations.index', ['date' => '2026-07-07']))
        ->assertSuccessful()
        ->assertSee('9:00 AM')
        ->assertSee('10:00 AM')
        ->assertSee('11:00 AM')
        ->assertSee('title="Not Available"', false);
});

test('users cannot book an already reserved time slot', function () {
    $startsAt = Carbon::parse('2026-07-08 10:00:00', 'UTC');

    Reservation::factory()->create([
        'starts_at' => $startsAt,
        'ends_at' => $startsAt->copy()->addHour(),
    ]);

    $this->get(route('reservations.index', ['date' => '2026-07-08']))
        ->assertSuccessful()
        ->assertSee('10:00 AM')
        ->assertSee('title="Reserved"', false)
        ->assertSee('disabled', false);

    $this->post(route('reservations.store'), [
        'name' => 'John Smith',
        'email' => 'john@example.com',
        'starts_at' => $startsAt->toIso8601String(),
    ])
        ->assertSessionHasErrors('starts_at');

    expect(Reservation::query()->count())->toBe(1);
});

test('users cannot book reservations on weekends', function () {
    $startsAt = Carbon::parse('2026-07-11 10:00:00', 'UTC');

    $this->post(route('reservations.store'), [
        'name' => 'John Smith',
        'email' => 'john@example.com',
        'starts_at' => $startsAt->toIso8601String(),
    ])
        ->assertSessionHasErrors('starts_at');
});

test('google calendar busy periods block available slots', function () {
    $this->mock(GoogleCalendarService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->andReturn(true);
        $mock->shouldReceive('getBusyPeriods')->andReturn(collect([
            [
                'start' => Carbon::parse('2026-07-08 10:00:00', 'UTC'),
                'end' => Carbon::parse('2026-07-08 11:00:00', 'UTC'),
            ],
        ]));
        $mock->shouldReceive('createEvent')->andReturn('google-event-456');
    });

    $response = $this->get(route('reservations.index', ['date' => '2026-07-08']));

    $response->assertSuccessful();
    $response->assertSee('9:00 AM');
    $response->assertSee('10:00 AM');
    $response->assertSee('11:00 AM');
    $response->assertSee('disabled', false);
});

test('google calendar event is created when calendar is configured', function () {
    $startsAt = Carbon::parse('2026-07-08 14:00:00', 'UTC');

    $this->mock(GoogleCalendarService::class, function ($mock) {
        $mock->shouldReceive('isConfigured')->andReturn(true);
        $mock->shouldReceive('getBusyPeriods')->andReturn(Collection::make());
        $mock->shouldReceive('createEvent')
            ->once()
            ->andReturnUsing(function ($reservation) {
                expect($reservation)->toBeInstanceOf(Reservation::class);

                return 'google-event-789';
            });
    });

    $this->post(route('reservations.store'), [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'starts_at' => $startsAt->toIso8601String(),
    ])->assertRedirect();

    $this->assertDatabaseHas('reservations', [
        'email' => 'jane@example.com',
        'google_event_id' => 'google-event-789',
    ]);
});

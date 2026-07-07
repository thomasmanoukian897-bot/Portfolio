<?php

use App\Models\Reservation;
use App\Services\GoogleCalendarClientFactory;
use App\Services\GoogleCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

uses(RefreshDatabase::class);

beforeEach(function () {
    if (! filter_var(env('GOOGLE_CALENDAR_INTEGRATION', false), FILTER_VALIDATE_BOOL)) {
        $this->markTestSkipped('Set GOOGLE_CALENDAR_INTEGRATION=true to run live Google Calendar tests.');
    }

    $credentialsPath = app(GoogleCalendarClientFactory::class)->credentialsPath();

    if ($credentialsPath === '' || ! is_readable($credentialsPath)) {
        $this->markTestSkipped('Google Calendar credentials file is not readable.');
    }

    if (! filled(config('services.google.calendar_id'))) {
        $this->markTestSkipped('GOOGLE_CALENDAR_ID is not configured.');
    }

    config([
        'reservations.timezone' => config('reservations.timezone', 'UTC'),
    ]);
});

test('google calendar service account can query free busy periods', function () {
    $service = app(GoogleCalendarService::class);

    expect($service->isConfigured())->toBeTrue();

    $periods = $service->getBusyPeriods(now(), now()->addDays(7));

    expect($periods)->toBeInstanceOf(Collection::class);
});

test('google calendar service account can create and delete events', function () {
    $service = app(GoogleCalendarService::class);
    $startsAt = now()->addDays(14)->startOfHour()->addHours(3);
    $endsAt = $startsAt->copy()->addHour();

    $reservation = Reservation::factory()->make([
        'name' => 'Integration Test Reservation',
        'email' => 'integration-test@example.com',
        'starts_at' => $startsAt,
        'ends_at' => $endsAt,
        'notes' => 'Automated integration test event. Safe to delete.',
    ]);

    $eventId = $service->createEvent($reservation);

    expect($eventId)->not->toBeEmpty();

    $calendar = app(GoogleCalendarClientFactory::class)->make();
    $event = $calendar->getEvent(config('services.google.calendar_id'), $eventId);

    expect($event->getSummary())->toBe('Reservation: Integration Test Reservation');

    $calendar->deleteEvent(config('services.google.calendar_id'), $eventId);
})->group('google-calendar-integration');

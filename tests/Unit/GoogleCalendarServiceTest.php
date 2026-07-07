<?php

use App\Models\Reservation;
use App\Services\GoogleCalendarClientFactory;
use App\Services\GoogleCalendarGateway;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\FreeBusyResponse;
use Google\Service\Calendar\TimePeriod;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    config([
        'services.google.calendar_id' => 'calendar@group.calendar.google.com',
        'reservations.timezone' => 'UTC',
    ]);
});

test('is configured returns false without calendar id', function () {
    config([
        'services.google.calendar_id' => null,
        'services.google.calendar_credentials' => null,
    ]);

    expect(app(GoogleCalendarService::class)->isConfigured())->toBeFalse();
});

test('is configured returns false for oauth credentials without refresh token', function () {
    $path = tempnam(sys_get_temp_dir(), 'google-oauth-');
    file_put_contents($path, json_encode([
        'web' => [
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
        ],
    ]));

    config([
        'services.google.calendar_id' => 'calendar@group.calendar.google.com',
        'services.google.calendar_credentials' => $path,
        'services.google.calendar_refresh_token' => null,
    ]);

    expect(app(GoogleCalendarService::class)->isConfigured())->toBeFalse();

    unlink($path);
});

test('is configured returns true for service account credentials', function () {
    $path = tempnam(sys_get_temp_dir(), 'google-sa-');
    file_put_contents($path, json_encode([
        'type' => 'service_account',
        'client_email' => 'calendar@project.iam.gserviceaccount.com',
        'private_key' => "-----BEGIN PRIVATE KEY-----\ntest\n-----END PRIVATE KEY-----\n",
    ]));

    config([
        'services.google.calendar_id' => 'calendar@group.calendar.google.com',
        'services.google.calendar_credentials' => $path,
    ]);

    expect(app(GoogleCalendarService::class)->isConfigured())->toBeTrue();

    unlink($path);
});

test('is configured returns true for oauth credentials with refresh token', function () {
    $path = tempnam(sys_get_temp_dir(), 'google-oauth-');
    file_put_contents($path, json_encode([
        'web' => [
            'client_id' => 'test-client-id',
            'client_secret' => 'test-client-secret',
        ],
    ]));

    config([
        'services.google.calendar_id' => 'calendar@group.calendar.google.com',
        'services.google.calendar_credentials' => $path,
        'services.google.calendar_refresh_token' => 'test-refresh-token',
    ]);

    expect(app(GoogleCalendarService::class)->isConfigured())->toBeTrue();

    unlink($path);
});

test('is configured returns false when refresh token is an access token', function () {
    config([
        'services.google.calendar_id' => 'calendar@group.calendar.google.com',
        'services.google.calendar_credentials' => 'storage/missing-credentials.json',
        'services.google.client_id' => 'test-client-id',
        'services.google.client_secret' => 'test-client-secret',
        'services.google.calendar_refresh_token' => 'ya29.access-token-not-refresh-token',
    ]);

    expect(app(GoogleCalendarService::class)->isConfigured())->toBeFalse();
});

test('get busy periods returns empty collection when not configured', function () {
    config([
        'services.google.calendar_id' => null,
        'services.google.calendar_credentials' => null,
    ]);

    $periods = app(GoogleCalendarService::class)->getBusyPeriods(now(), now()->addDay());

    expect($periods)->toBeEmpty();
});

test('get busy periods maps google free busy response', function () {
    $period = new TimePeriod;
    $period->setStart('2026-07-08T10:00:00Z');
    $period->setEnd('2026-07-08T11:00:00Z');

    $calendarBusy = new class($period)
    {
        public function __construct(private TimePeriod $period) {}

        public function getBusy(): array
        {
            return [$this->period];
        }
    };

    $response = Mockery::mock(FreeBusyResponse::class);
    $response->shouldReceive('getCalendars')->andReturn([
        'calendar@group.calendar.google.com' => $calendarBusy,
    ]);

    $gateway = Mockery::mock(GoogleCalendarGateway::class);
    $gateway->shouldReceive('queryFreeBusy')->once()->andReturn($response);

    $factory = Mockery::mock(GoogleCalendarClientFactory::class);
    $factory->shouldReceive('hasValidAuthentication')->andReturn(true);
    $factory->shouldReceive('make')->andReturn($gateway);

    $periods = (new GoogleCalendarService($factory))->getBusyPeriods(
        Carbon::parse('2026-07-08 00:00:00', 'UTC'),
        Carbon::parse('2026-07-09 00:00:00', 'UTC'),
    );

    expect($periods)->toHaveCount(1);
    expect($periods->first()['start']->format('Y-m-d H:i:s'))->toBe('2026-07-08 10:00:00');
    expect($periods->first()['end']->format('Y-m-d H:i:s'))->toBe('2026-07-08 11:00:00');
});

test('create event omits attendees for service accounts', function () {
    $createdEvent = null;
    $insertOptions = null;

    $gateway = Mockery::mock(GoogleCalendarGateway::class);
    $gateway->shouldReceive('insertEvent')
        ->once()
        ->withArgs(function (string $calendarId, Event $event, array $options) use (&$createdEvent, &$insertOptions) {
            $createdEvent = $event;
            $insertOptions = $options;

            return $calendarId === 'calendar@group.calendar.google.com'
                && $options === ['sendUpdates' => 'none'];
        })
        ->andReturnUsing(function () {
            $response = new Event;
            $response->setId('google-event-123');

            return $response;
        });

    $factory = Mockery::mock(GoogleCalendarClientFactory::class);
    $factory->shouldReceive('hasValidAuthentication')->andReturn(true);
    $factory->shouldReceive('usesServiceAccount')->andReturn(true);
    $factory->shouldReceive('make')->andReturn($gateway);

    $service = new GoogleCalendarService($factory);

    $reservation = Reservation::factory()->make([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'starts_at' => Carbon::parse('2026-07-08 10:00:00', 'UTC'),
        'ends_at' => Carbon::parse('2026-07-08 11:00:00', 'UTC'),
    ]);

    $eventId = $service->createEvent($reservation);

    expect($eventId)->toBe('google-event-123');
    expect($createdEvent?->getAttendees())->toBeEmpty();
    expect($insertOptions)->toBe(['sendUpdates' => 'none']);
    expect($createdEvent?->getSummary())->toBe('Reservation: Jane Doe');
});

test('create event includes attendees for oauth credentials', function () {
    $createdEvent = null;
    $insertOptions = null;

    $gateway = Mockery::mock(GoogleCalendarGateway::class);
    $gateway->shouldReceive('insertEvent')
        ->once()
        ->withArgs(function (string $calendarId, Event $event, array $options) use (&$createdEvent, &$insertOptions) {
            $createdEvent = $event;
            $insertOptions = $options;

            return $calendarId === 'calendar@group.calendar.google.com'
                && $options === ['sendUpdates' => 'all'];
        })
        ->andReturnUsing(function () {
            $response = new Event;
            $response->setId('google-event-456');

            return $response;
        });

    $factory = Mockery::mock(GoogleCalendarClientFactory::class);
    $factory->shouldReceive('hasValidAuthentication')->andReturn(true);
    $factory->shouldReceive('usesServiceAccount')->andReturn(false);
    $factory->shouldReceive('make')->andReturn($gateway);

    $service = new GoogleCalendarService($factory);

    $reservation = Reservation::factory()->make([
        'email' => 'jane@example.com',
    ]);

    $eventId = $service->createEvent($reservation);

    expect($eventId)->toBe('google-event-456');
    expect($createdEvent?->getAttendees())->toBe([['email' => 'jane@example.com']]);
    expect($insertOptions)->toBe(['sendUpdates' => 'all']);
});

test('create event throws when calendar is not configured', function () {
    $factory = Mockery::mock(GoogleCalendarClientFactory::class);
    $factory->shouldReceive('hasValidAuthentication')->andReturn(false);

    $service = new GoogleCalendarService($factory);

    expect(fn () => $service->createEvent(Reservation::factory()->make()))
        ->toThrow(RuntimeException::class, 'Google Calendar is not configured.');
});

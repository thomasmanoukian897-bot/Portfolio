<?php

namespace App\Services;

use App\Contracts\GoogleCalendarService as GoogleCalendarServiceContract;
use App\Models\Reservation;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Google\Service\Calendar\FreeBusyRequest;
use Google\Service\Calendar\TimePeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class GoogleCalendarService implements GoogleCalendarServiceContract
{
    public function __construct(
        private GoogleCalendarClientFactory $clientFactory,
    ) {}

    public function isConfigured(): bool
    {
        if (! filled(config('services.google.calendar_id'))) {
            return false;
        }

        return $this->clientFactory->hasValidAuthentication();
    }

    /**
     * @return Collection<int, array{start: CarbonInterface, end: CarbonInterface}>
     */
    public function getBusyPeriods(CarbonInterface $rangeStart, CarbonInterface $rangeEnd): Collection
    {
        if (! $this->isConfigured()) {
            return collect();
        }

        try {
            $calendar = $this->clientFactory->make();
            $timezone = config('reservations.timezone');

            $request = new FreeBusyRequest([
                'timeMin' => $rangeStart->copy()->timezone($timezone)->toRfc3339String(),
                'timeMax' => $rangeEnd->copy()->timezone($timezone)->toRfc3339String(),
                'timeZone' => $timezone,
                'items' => [
                    ['id' => config('services.google.calendar_id')],
                ],
            ]);

            $response = $calendar->queryFreeBusy($request);
            $calendarBusy = $response->getCalendars()[config('services.google.calendar_id')] ?? null;

            if ($calendarBusy === null) {
                return collect();
            }

            return collect($calendarBusy->getBusy() ?? [])
                ->map(function (TimePeriod $period) use ($timezone) {
                    return [
                        'start' => Carbon::parse($period->getStart())->timezone($timezone),
                        'end' => Carbon::parse($period->getEnd())->timezone($timezone),
                    ];
                });
        } catch (Throwable $exception) {
            Log::error('Failed to fetch Google Calendar busy periods', [
                'exception' => $exception->getMessage(),
            ]);

            return collect();
        }
    }

    public function createEvent(Reservation $reservation): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Google Calendar is not configured.');
        }

        $calendar = $this->clientFactory->make();
        $timezone = config('reservations.timezone');
        $usesServiceAccount = $this->clientFactory->usesServiceAccount();

        $event = new Event([
            'summary' => "Reservation: {$reservation->name}",
            'description' => trim("Email: {$reservation->email}\n".($reservation->notes ? "Notes: {$reservation->notes}" : '')),
            'start' => new EventDateTime([
                'dateTime' => $reservation->starts_at->timezone($timezone)->toRfc3339String(),
                'timeZone' => $timezone,
            ]),
            'end' => new EventDateTime([
                'dateTime' => $reservation->ends_at->timezone($timezone)->toRfc3339String(),
                'timeZone' => $timezone,
            ]),
        ]);

        if (! $usesServiceAccount) {
            $event->setAttendees([
                ['email' => $reservation->email],
            ]);
        }

        $created = $calendar->insertEvent(
            config('services.google.calendar_id'),
            $event,
            ['sendUpdates' => $usesServiceAccount ? 'none' : 'all'],
        );

        $eventId = $created->getId();

        if ($eventId === null) {
            throw new RuntimeException('Google Calendar did not return an event ID.');
        }

        return $eventId;
    }

    public function updateEvent(Reservation $reservation): void
    {
        if (! $this->isConfigured() || $reservation->google_event_id === null) {
            return;
        }

        try {
            $calendar = $this->clientFactory->make();
            $timezone = config('reservations.timezone');

            $event = $calendar->getEvent(
                config('services.google.calendar_id'),
                $reservation->google_event_id,
            );

            $event->setStart(new EventDateTime([
                'dateTime' => $reservation->starts_at->timezone($timezone)->toRfc3339String(),
                'timeZone' => $timezone,
            ]));
            $event->setEnd(new EventDateTime([
                'dateTime' => $reservation->ends_at->timezone($timezone)->toRfc3339String(),
                'timeZone' => $timezone,
            ]));

            $calendar->updateEvent(
                config('services.google.calendar_id'),
                $reservation->google_event_id,
                $event,
                ['sendUpdates' => $this->clientFactory->usesServiceAccount() ? 'none' : 'all'],
            );
        } catch (Throwable $exception) {
            Log::error('Failed to update Google Calendar event for reservation', [
                'reservation_id' => $reservation->id,
                'google_event_id' => $reservation->google_event_id,
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    public function deleteEvent(Reservation $reservation): void
    {
        if (! $this->isConfigured() || $reservation->google_event_id === null) {
            return;
        }

        try {
            $calendar = $this->clientFactory->make();
            $calendar->deleteEvent(
                config('services.google.calendar_id'),
                $reservation->google_event_id,
            );
        } catch (Throwable $exception) {
            Log::error('Failed to delete Google Calendar event for reservation', [
                'reservation_id' => $reservation->id,
                'google_event_id' => $reservation->google_event_id,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}

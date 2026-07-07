<?php

namespace App\Services;

use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\FreeBusyRequest;
use Google\Service\Calendar\FreeBusyResponse;

class GoogleCalendarGateway
{
    public function __construct(
        private Calendar $calendar,
    ) {}

    public function queryFreeBusy(FreeBusyRequest $request): FreeBusyResponse
    {
        return $this->calendar->freebusy->query($request);
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function insertEvent(string $calendarId, Event $event, array $options = []): Event
    {
        return $this->calendar->events->insert($calendarId, $event, $options);
    }

    public function getEvent(string $calendarId, string $eventId): Event
    {
        return $this->calendar->events->get($calendarId, $eventId);
    }

    public function deleteEvent(string $calendarId, string $eventId): void
    {
        $this->calendar->events->delete($calendarId, $eventId);
    }
}

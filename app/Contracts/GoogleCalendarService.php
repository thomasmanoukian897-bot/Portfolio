<?php

namespace App\Contracts;

use App\Models\Reservation;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

interface GoogleCalendarService
{
    public function isConfigured(): bool;

    /**
     * @return Collection<int, array{start: CarbonInterface, end: CarbonInterface}>
     */
    public function getBusyPeriods(CarbonInterface $rangeStart, CarbonInterface $rangeEnd): Collection;

    public function createEvent(Reservation $reservation): string;

    public function deleteEvent(Reservation $reservation): void;
}

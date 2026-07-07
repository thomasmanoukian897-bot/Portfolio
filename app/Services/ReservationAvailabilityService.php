<?php

namespace App\Services;

use App\Contracts\GoogleCalendarService;
use App\Models\Reservation;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ReservationAvailabilityService
{
    public function __construct(
        private GoogleCalendarService $googleCalendar,
    ) {}

    /**
     * @return Collection<int, array{starts_at: CarbonInterface, available: bool, unavailable_reason: string|null}>
     */
    public function slotsForDate(
        CarbonInterface $date,
        ?Reservation $excluding = null,
        bool $allowPast = false,
        bool $allowPastDates = false,
    ): Collection {
        $timezone = config('reservations.timezone');
        $day = $date->copy()->timezone($timezone)->startOfDay();

        if ($day->isWeekend()) {
            return collect();
        }

        if ($allowPastDates) {
            $maxDate = now()->timezone($timezone)->startOfDay()->addDays(config('reservations.advance_days'));

            if ($day->gt($maxDate)) {
                return collect();
            }
        } elseif ($this->isDateOutOfRange($day)) {
            return collect();
        }

        $slots = $this->generateSlotsForDay($day);
        $busyPeriods = $this->getBusyPeriodsForDay($day, $excluding);
        $now = now()->timezone($timezone);

        return $slots->map(function (CarbonInterface $slot) use ($busyPeriods, $now, $excluding, $timezone, $allowPast) {
            $slotEnd = $slot->copy()->addMinutes(config('reservations.duration_minutes'));
            $isPast = $slot->lt($now);
            $isReserved = $this->overlapsAnyPeriod($slot, $slotEnd, $busyPeriods);

            if ($excluding !== null && $slot->equalTo($excluding->starts_at->copy()->timezone($timezone)->seconds(0))) {
                $isReserved = false;
            }

            $unavailableReason = null;

            if ($isPast && ! $allowPast) {
                $unavailableReason = 'past';
            } elseif ($isReserved) {
                $unavailableReason = 'reserved';
            }

            return [
                'starts_at' => $slot,
                'available' => $unavailableReason === null,
                'unavailable_reason' => $unavailableReason,
            ];
        })->values();
    }

    /**
     * @return Collection<int, CarbonInterface>
     */
    public function availableSlotsForDate(CarbonInterface $date): Collection
    {
        return $this->slotsForDate($date)
            ->filter(fn (array $slot) => $slot['available'])
            ->pluck('starts_at')
            ->values();
    }

    public function isSlotAvailable(CarbonInterface $startsAt): bool
    {
        $timezone = config('reservations.timezone');
        $slot = $startsAt->copy()->timezone($timezone)->seconds(0);

        return $this->availableSlotsForDate($slot)->contains(
            fn (CarbonInterface $available) => $available->equalTo($slot),
        );
    }

    public function isSlotAvailableForUpdate(CarbonInterface $startsAt, Reservation $reservation): bool
    {
        $timezone = config('reservations.timezone');
        $slot = $startsAt->copy()->timezone($timezone)->seconds(0);

        return $this->slotsForDate($slot, $reservation, allowPast: true, allowPastDates: true)
            ->contains(fn (array $entry) => $entry['starts_at']->equalTo($slot) && $entry['available']);
    }

    private function isDateOutOfRange(CarbonInterface $day): bool
    {
        $timezone = config('reservations.timezone');
        $today = now()->timezone($timezone)->startOfDay();
        $maxDate = $today->copy()->addDays(config('reservations.advance_days'));

        return $day->lt($today) || $day->gt($maxDate);
    }

    /**
     * @return Collection<int, CarbonInterface>
     */
    private function generateSlotsForDay(CarbonInterface $day): Collection
    {
        $timezone = config('reservations.timezone');
        $startHour = config('reservations.start_hour');
        $endHour = config('reservations.end_hour');
        $interval = config('reservations.slot_interval_minutes');
        $duration = config('reservations.duration_minutes');

        $slots = collect();
        $cursor = $day->copy()->timezone($timezone)->setTime($startHour, 0);
        $dayEnd = $day->copy()->timezone($timezone)->setTime($endHour, 0);

        while ($cursor->copy()->addMinutes($duration)->lte($dayEnd)) {
            $slots->push($cursor->copy());

            $cursor->addMinutes($interval);
        }

        return $slots;
    }

    /**
     * @return Collection<int, array{start: CarbonInterface, end: CarbonInterface}>
     */
    private function getBusyPeriodsForDay(CarbonInterface $day, ?Reservation $excluding = null): Collection
    {
        $timezone = config('reservations.timezone');
        $rangeStart = $day->copy()->timezone($timezone)->startOfDay();
        $rangeEnd = $day->copy()->timezone($timezone)->endOfDay();

        $googleBusy = $this->googleCalendar->getBusyPeriods($rangeStart, $rangeEnd);

        $localBusy = Reservation::query()
            ->when($excluding !== null, fn ($query) => $query->where('id', '!=', $excluding->id))
            ->where('starts_at', '<', $rangeEnd)
            ->where('ends_at', '>', $rangeStart)
            ->get(['starts_at', 'ends_at'])
            ->map(fn (Reservation $reservation) => [
                'start' => $reservation->starts_at->timezone($timezone),
                'end' => $reservation->ends_at->timezone($timezone),
            ]);

        return $googleBusy->merge($localBusy);
    }

    /**
     * @param  Collection<int, array{start: CarbonInterface, end: CarbonInterface}>  $periods
     */
    private function overlapsAnyPeriod(CarbonInterface $start, CarbonInterface $end, Collection $periods): bool
    {
        return $periods->contains(function (array $period) use ($start, $end) {
            return $start->lt($period['end']) && $end->gt($period['start']);
        });
    }
}

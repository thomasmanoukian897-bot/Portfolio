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
     * @return Collection<int, CarbonInterface>
     */
    public function availableSlotsForDate(CarbonInterface $date): Collection
    {
        $timezone = config('reservations.timezone');
        $day = $date->copy()->timezone($timezone)->startOfDay();

        if ($this->isDateOutOfRange($day)) {
            return collect();
        }

        if ($day->isWeekend()) {
            return collect();
        }

        $slots = $this->generateSlotsForDay($day);
        $busyPeriods = $this->getBusyPeriodsForDay($day);

        return $slots->filter(function (CarbonInterface $slot) use ($busyPeriods) {
            $slotEnd = $slot->copy()->addMinutes(config('reservations.duration_minutes'));

            return ! $this->overlapsAnyPeriod($slot, $slotEnd, $busyPeriods);
        })->values();
    }

    public function isSlotAvailable(CarbonInterface $startsAt): bool
    {
        $timezone = config('reservations.timezone');
        $slot = $startsAt->copy()->timezone($timezone)->seconds(0);

        return $this->availableSlotsForDate($slot)->contains(
            fn (CarbonInterface $available) => $available->equalTo($slot),
        );
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
            if ($cursor->gte(now()->timezone($timezone))) {
                $slots->push($cursor->copy());
            }

            $cursor->addMinutes($interval);
        }

        return $slots;
    }

    /**
     * @return Collection<int, array{start: CarbonInterface, end: CarbonInterface}>
     */
    private function getBusyPeriodsForDay(CarbonInterface $day): Collection
    {
        $timezone = config('reservations.timezone');
        $rangeStart = $day->copy()->timezone($timezone)->startOfDay();
        $rangeEnd = $day->copy()->timezone($timezone)->endOfDay();

        $googleBusy = $this->googleCalendar->getBusyPeriods($rangeStart, $rangeEnd);

        $localBusy = Reservation::query()
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

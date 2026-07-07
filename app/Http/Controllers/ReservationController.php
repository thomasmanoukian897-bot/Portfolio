<?php

namespace App\Http\Controllers;

use App\Contracts\GoogleCalendarService;
use App\Http\Requests\StoreReservationRequest;
use App\Models\Reservation;
use App\Services\ReservationAvailabilityService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReservationController extends Controller
{
    public function index(Request $request, ReservationAvailabilityService $availability): View
    {
        $timezone = config('reservations.timezone');
        $selectedDate = $this->resolveSelectedDate($request, $timezone);
        $slots = $availability->availableSlotsForDate($selectedDate);

        $minDate = now()->timezone($timezone)->toDateString();
        $maxDate = now()->timezone($timezone)->addDays(config('reservations.advance_days'))->toDateString();

        return view('reservations.index', [
            'selectedDate' => $selectedDate->toDateString(),
            'minDate' => $minDate,
            'maxDate' => $maxDate,
            'slots' => $slots,
            'timezone' => $timezone,
            'durationMinutes' => config('reservations.duration_minutes'),
            'calendarConfigured' => app(GoogleCalendarService::class)->isConfigured(),
        ]);
    }

    public function store(
        StoreReservationRequest $request,
        ReservationAvailabilityService $availability,
        GoogleCalendarService $googleCalendar,
    ): RedirectResponse {
        $validated = $request->validated();
        $timezone = config('reservations.timezone');
        $startsAt = Carbon::parse($validated['starts_at'])->timezone($timezone)->seconds(0);
        $endsAt = $startsAt->copy()->addMinutes(config('reservations.duration_minutes'));

        if (! $availability->isSlotAvailable($startsAt)) {
            return back()
                ->withInput()
                ->withErrors(['starts_at' => 'This time slot is no longer available. Please choose another.']);
        }

        try {
            $reservation = DB::transaction(function () use ($validated, $startsAt, $endsAt, $request) {
                $overlapExists = Reservation::query()
                    ->where('starts_at', '<', $endsAt)
                    ->where('ends_at', '>', $startsAt)
                    ->lockForUpdate()
                    ->exists();

                if ($overlapExists) {
                    return null;
                }

                return Reservation::query()->create([
                    'user_id' => $request->user()?->id,
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                    'notes' => $validated['notes'] ?? null,
                ]);
            });
        } catch (Throwable $exception) {
            Log::error('Failed to create reservation', [
                'exception' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['starts_at' => 'Unable to complete your reservation. Please try again.']);
        }

        if ($reservation === null) {
            return back()
                ->withInput()
                ->withErrors(['starts_at' => 'This time slot is no longer available. Please choose another.']);
        }

        if ($googleCalendar->isConfigured()) {
            try {
                $eventId = $googleCalendar->createEvent($reservation);
                $reservation->forceFill(['google_event_id' => $eventId])->save();
            } catch (Throwable $exception) {
                Log::error('Failed to create Google Calendar event for reservation', [
                    'reservation_id' => $reservation->id,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }

        return redirect()
            ->route('reservations.index', ['date' => $startsAt->toDateString()])
            ->with('success', 'Your reservation has been confirmed for '.$startsAt->format('l, F j, Y \a\t g:i A').'.');
    }

    public function destroy(Reservation $reservation, GoogleCalendarService $googleCalendar): RedirectResponse
    {
        $this->authorize('delete', $reservation);

        $googleCalendar->deleteEvent($reservation);

        $reservation->delete();

        return redirect()
            ->route('library.index', ['section' => 'bookings'])
            ->with('status', 'Your booking has been cancelled.');
    }

    private function resolveSelectedDate(Request $request, string $timezone): Carbon
    {
        $date = $request->string('date')->toString();

        if ($date !== '') {
            return Carbon::parse($date, $timezone)->startOfDay();
        }

        return now()->timezone($timezone)->startOfDay();
    }
}

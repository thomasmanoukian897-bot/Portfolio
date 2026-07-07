<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\GoogleCalendarService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateBookingRequest;
use App\Models\Reservation;
use App\Services\ReservationAvailabilityService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Reservation::class);

        $bookings = Reservation::query()
            ->with('user')
            ->when($request->string('search')->isNotEmpty(), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('starts_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'timezone' => config('reservations.timezone'),
        ]);
    }

    public function edit(Request $request, Reservation $reservation, ReservationAvailabilityService $availability): View
    {
        $this->authorize('update', $reservation);

        $timezone = config('reservations.timezone');
        $dateInput = $request->string('date')->toString();

        if ($dateInput !== '') {
            $selectedDate = Carbon::parse($dateInput, $timezone)->startOfDay();
        } else {
            $selectedDate = $reservation->starts_at->copy()->timezone($timezone)->startOfDay();
        }

        $maxDate = now()->timezone($timezone)->addDays(config('reservations.advance_days'))->toDateString();
        $slots = $availability->slotsForDate($selectedDate, $reservation, allowPast: true, allowPastDates: true);

        return view('admin.bookings.edit', [
            'reservation' => $reservation,
            'selectedDate' => $selectedDate->toDateString(),
            'maxDate' => $maxDate,
            'slots' => $slots,
            'timezone' => $timezone,
            'selectedStartsAt' => old('starts_at', $reservation->starts_at->toIso8601String()),
        ]);
    }

    public function update(
        UpdateBookingRequest $request,
        Reservation $reservation,
        GoogleCalendarService $googleCalendar,
    ): RedirectResponse {
        $timezone = config('reservations.timezone');
        $startsAt = Carbon::parse($request->validated('starts_at'))->timezone($timezone)->seconds(0);
        $endsAt = $startsAt->copy()->addMinutes(config('reservations.duration_minutes'));

        $reservation->update([
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        $googleCalendar->updateEvent($reservation->fresh());

        return redirect()
            ->route('admin.bookings.index')
            ->with('status', "Booking for {$reservation->name} updated successfully.");
    }

    public function destroy(Reservation $reservation, GoogleCalendarService $googleCalendar): RedirectResponse
    {
        $this->authorize('delete', $reservation);

        $name = $reservation->name;

        $googleCalendar->deleteEvent($reservation);
        $reservation->delete();

        return redirect()
            ->route('admin.bookings.index')
            ->with('status', "Booking for {$name} cancelled successfully.");
    }
}

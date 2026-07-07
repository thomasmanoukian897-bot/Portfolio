<?php

namespace App\Http\Requests\Admin;

use App\Models\Reservation;
use App\Services\ReservationAvailabilityService;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('reservation')) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'starts_at' => ['required', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            /** @var Reservation $reservation */
            $reservation = $this->route('reservation');
            $timezone = config('reservations.timezone');
            $startsAt = Carbon::parse($this->input('starts_at'))->timezone($timezone);

            if ($startsAt->isWeekend()) {
                $validator->errors()->add('starts_at', 'Reservations are not available on weekends.');

                return;
            }

            if (! app(ReservationAvailabilityService::class)->isSlotAvailableForUpdate($startsAt, $reservation)) {
                $validator->errors()->add('starts_at', 'This time slot is not available. Please choose another.');
            }
        });
    }
}

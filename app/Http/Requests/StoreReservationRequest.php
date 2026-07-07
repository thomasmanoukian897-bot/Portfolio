<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'starts_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $timezone = config('reservations.timezone');
            $startsAt = Carbon::parse($this->input('starts_at'))->timezone($timezone);
            $minDate = now()->timezone($timezone)->startOfDay();
            $maxDate = $minDate->copy()->addDays(config('reservations.advance_days'))->endOfDay();

            if ($startsAt->lt($minDate) || $startsAt->gt($maxDate)) {
                $validator->errors()->add('starts_at', 'Please choose a date within the allowed booking window.');
            }

            if ($startsAt->isWeekend()) {
                $validator->errors()->add('starts_at', 'Reservations are not available on weekends.');
            }
        });
    }
}

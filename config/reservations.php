<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Reservation Scheduling
    |--------------------------------------------------------------------------
    */

    'timezone' => env('RESERVATION_TIMEZONE', config('app.timezone', 'UTC')),

    'duration_minutes' => (int) env('RESERVATION_DURATION_MINUTES', 60),

    'start_hour' => (int) env('RESERVATION_START_HOUR', 9),

    'end_hour' => (int) env('RESERVATION_END_HOUR', 17),

    'advance_days' => (int) env('RESERVATION_ADVANCE_DAYS', 30),

    'slot_interval_minutes' => (int) env('RESERVATION_SLOT_INTERVAL_MINUTES', 60),

];

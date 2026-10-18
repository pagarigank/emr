<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Appointment Slot Duration
    |--------------------------------------------------------------------------
    |
    | How long each booking slot lasts, in minutes.
    |
    */

    'slot_minutes' => env('APPOINTMENT_SLOT_MINUTES', 30),

    /*
    |--------------------------------------------------------------------------
    | Working Hours
    |--------------------------------------------------------------------------
    |
    | The daily window during which appointments can be booked. Slots are
    | generated between these times. Format is 24h "H:i".
    |
    */

    'working_start' => env('APPOINTMENT_WORKING_START', '08:00'),
    'working_end'   => env('APPOINTMENT_WORKING_END', '17:00'),

    /*
    |--------------------------------------------------------------------------
    | Public Booking Tenant
    |--------------------------------------------------------------------------
    |
    | The public booking page has no login, so it must resolve a tenant
    | explicitly. Defaults to the first hospital (tenant) in the system.
    |
    */

    'public_tenant_id' => env('PUBLIC_BOOKING_TENANT_ID', null),

];

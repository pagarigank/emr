<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('philhealth:poll-claims')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/philhealth-poll.log'))
    ->description('Poll all submitted PhilHealth claims for status updates');

Schedule::command('billing:auto-invoice')
    ->dailyAt('23:59')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/billing-auto-invoice.log'))
    ->description('Auto-create invoices for completed billable service requests and discharged room charges');

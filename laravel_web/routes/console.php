<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule automatic expiration of overdue Ride and Delivery requests every minute
Schedule::command('requests:expire-pending')->everyMinute()->withoutOverlapping();


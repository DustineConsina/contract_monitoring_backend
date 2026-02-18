<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule automated tasks
Schedule::command('payments:send-reminders')->daily();
Schedule::command('contracts:send-expiry-notifications')->daily();
Schedule::command('payments:calculate-overdue-interest')->daily();
Schedule::command('contracts:update-expired')->daily();

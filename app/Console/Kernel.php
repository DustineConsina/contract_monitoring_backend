<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run demand letter generation daily at 8 AM
        $schedule->command('payments:generate-demand-letters')
                 ->dailyAt('08:00')
                 ->description('Generate and send demand letters for overdue payments');

        // Update payment statuses to overdue daily at 7 AM
        $schedule->command('payments:update-overdue-status')
                 ->dailyAt('07:00')
                 ->description('Update overdue payment statuses');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

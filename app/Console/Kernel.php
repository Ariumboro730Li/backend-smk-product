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
        $schedule->command('oss:update-email')->everyThirtyMinutes();
        $schedule->command('spionam:check-company-register')->everyMinute();
        $schedule->command('mail:yearly-report')->dailyAt('01:00')->runInBackground();
        $schedule->command('reminder:expired-request')->dailyAt('01:00')->runInBackground();
        $schedule->command('app:check-certificate-expiration')->dailyAt('01:00')->runInBackground();
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

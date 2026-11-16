<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('communication:fee-reminders')->dailyAt('08:00')->withoutOverlapping();

        // Term status follows the term dates, so it needs re-deriving as the
        // year moves along. Early morning so anything reading the current term
        // during the school day sees the right one.
        $schedule->command('academic:sync-terms')->dailyAt('00:15')->withoutOverlapping();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

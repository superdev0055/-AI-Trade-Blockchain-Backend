<?php

namespace App\Console;

use App\Jobs\RefreshWeb3TransactionJob;
use App\Jobs\RunComputePledgeProfitsJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('RefreshWeb3TransactionCommand')
            ->everyMinute()
            ->withoutOverlapping()
            ->onOneServer()
            ->appendOutputTo(storage_path('logs/laravel.log'));

        $schedule->command('RunComputePledgeProfitsCommand')
            ->everyTenMinutes()
            ->withoutOverlapping()
            ->onOneServer()
            ->appendOutputTo(storage_path('logs/laravel.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}

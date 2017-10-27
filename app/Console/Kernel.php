<?php

namespace Twinleaf\Console;

use Twinleaf\Services\KinanCore;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by Twinleaf.
     *
     * @var array
     */
    protected $commands = [
    ];

    /**
     * Define Twinleaf's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $kinan = new KinanCore;

        $schedule->call(function () use ($kinan) {
            $kinan->start();
        })->everyMinute()->skip(function () use ($kinan) {
            return $kinan->isRunning();
        });

        $schedule->command('accounts:update')->everyMinute();
    }

    /**
     * Register the commands for Twinleaf.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

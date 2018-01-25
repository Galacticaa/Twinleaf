<?php

namespace Twinleaf\Console;

use Twinleaf\Services\KinanCore;

use Activity;
use Carbon\Carbon;
use Twinleaf\MapArea;
use Twinleaf\Accounts\Generator;
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
        // Trigger account creation via KinanCity
        $kinan = new KinanCore;

        $schedule->call(function () use ($kinan) {
            $kinan->start();
        })->everyMinute()->skip(function () use ($kinan) {
            return $kinan->isRunning();
        })->appendOutputTo(storage_path('logs/creation.log'));

        // Check for areas with fresh accounts
        $schedule->command('accounts:update')->everyMinute()
                 ->appendOutputTo(storage_path('logs/accounts.log'));

        // Generate new accounts for enabled areas that
        // haven't been regenerated in 2 days or more
        $schedule->call(function () {
            $time = Carbon::now()->format('l jS \\of F Y H:i:s');
            echo "--------------------------------------------------------\n";
            echo " Account Regeneration - running at {$time}\n";
            echo "--------------------------------------------------------\n";
            $scans = MapArea::enabled()->get();
            $output = '';

            foreach ($scans as $scan) {
                $log = Activity::whereContentType('map_area')
                    ->whereContentId($scan->id)
                    ->where('description', 'LIKE', '%regenerated.')
                    ->where('created_at', '>', Carbon::now()->subDays(2))
                    ->orderBy('created_at', 'DESC')
                    ->limit(4);

                if ($log->count()) {
                    continue;
                }

                $oldCount = $scan->accounts()->count();
                $output .= "Replacing {$oldCount} accounts for {$scan->name}\n";

                foreach ($scan->accounts as $account) {
                    $account->area()->dissociate();
                    $account->save();
                }

                $result = (new Generator($scan))->generate();

                $scan->writeLog('regenerate', sprintf(
                    '<code>[cron]</code> <a href="%s">%s</a>\'s accounts were regenerated.',
                    $scan->url(), $scan->name
                ), sprintf(
                    'Before: %s; After: %s',
                    $oldCount, $scan->accounts()->count()
                ));
            }

            $output .= "\n\n";
            file_put_contents(storage_path('logs/refresh.log', $output, FILE_APPEND));
        })->hourly();
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

<?php

namespace Twinleaf\Console\Commands;

use Twinleaf\MapArea;
use Illuminate\Console\Command;

class LogRestart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:restart {area}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Log a restart operation for the given area';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        list($map, $area) = explode('/', $this->argument('area'));

        $area = MapArea::whereSlug($area)->first();

        $area->applyUptimeMax()->setStartTime();
        $area->save();

        $area->writeLog('restart', sprintf(
            '<code>[cron]</code> Restarted <a href="%s">%s</a>.',
            $area->url(), $area->name
        ));
    }
}

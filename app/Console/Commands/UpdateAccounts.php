<?php

namespace Twinleaf\Console\Commands;

use Twinleaf\MapArea;
use Twinleaf\Setting;

use Illuminate\Console\Command;

class UpdateAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update accounts and restart applicable Map Areas';

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
        $areas = MapArea::dueUpdate()->withActivatedAccounts()->get();

        foreach ($areas as $area) {
            $this->updateArea($area);
        }
    }

    protected function updateArea(MapArea $area)
    {
        if (!$area->accounts) {
            $this->line("Skipping area {$area->name}, it has no accounts.");
            return;
        }

        $result = $this->writeAccounts($area);

        if (!$result) {
            if ($result === false) {
                $this->error("Failed to write accounts for {$area->name}");
            }
            return;
        }

        $this->restartArea($area);

        $this->info("Completed update for the {$area->slug} area.");
    }

    protected function writeAccounts(MapArea $area)
    {
        $csv = '';

        foreach ($area->accounts as $account) {
            $csv .= $account->format().PHP_EOL;
        }

        $path = storage_path("maps/rocketmap/config/{$area->map->code}/{$area->slug}.csv");

        if ($csv == file_get_contents($path)) {
            $this->line("Skipping area {$area}, accounts are identical.");
            return null;
        }

        Acitivty::log([
            'contentId' => $area->id,
            'contentType' => 'map_area',
            'action' => 'write',
            'description' => sprintf(
                '<code>[cron]</code> Writing %s accounts for <a href="%s">%s</a>.',
                count($area->accounts),
                route('maps.areas.show', ['map' => $area->map, 'area' => $area]),
                $area->name
            ),
        ]);

        return false === file_put_contents($path, $csv);
    }

    protected function restartArea(MapArea $area)
    {
        $pids = $area->getPids();

        if (!count($pids)) {
            return;
        }

        foreach ($pids as $pid) {
            system(sprintf("kill -15 %s", $pid));
        }

        $target->applyUptimeMax()->unsetStartTime()->save();

        sleep(2);

        $mapDir = storage_path('maps/rocketmap');
        $python = Setting::first()->python_command;

        $cmd_parts = [
            "cd {$mapDir} &&",
            "tmux new-session -s \"tla_{$area->slug}\" -d",
            "{$python} runserver.py -cf \"config/{$area->map->code}/{$area->slug}.ini\" 2>&1",
        ];

        system(implode(' ', $cmd_parts));

        $area->setStartTime()->save();
    }
}

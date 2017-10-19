<?php

namespace Twinleaf\Http\Controllers;

use Twinleaf\Map;
use Twinleaf\MapArea;
use Twinleaf\Setting;

class RocketMapController extends Controller
{
    public function download()
    {
        $mapRoot = storage_path('maps');
        $mapDir = $mapRoot.'/rocketmap';
        $checkFile = $mapDir.'/.twinleaf_downloaded';
        $rmRepo = 'https://github.com/RocketMap/RocketMap.git';

        if (file_exists($checkFile)) {
            return ['downloaded' => true];
        } elseif (!is_dir($mapRoot)) {
            exec('mkdir -p '.$mapRoot);
        } elseif (is_dir($mapDir)) {
            exec('rm -rf '.$mapDir);
        }

        exec("git clone {$rmRepo} {$mapDir} && touch {$checkFile}");

        return ['downloaded' => file_exists($checkFile)];
    }

    public function install()
    {
        $dir = storage_path('maps/rocketmap');
        $checkFile = $dir.'/.twinleaf_installed';

        if (!file_exists($checkFile)) {
            $pip = exec('pip -V | grep "python 2"') ? 'pip' : 'pip2';

            exec("cd {$dir} && {$pip} install -r requirements.txt");
            exec("cd {$dir} && npm install && npm run build && touch {$checkFile}");
        }

        return ['installed' => file_exists($checkFile)];
    }

    public function clean(Map $map) {
        usleep(800000);
        $mapDir = storage_path('maps/rocketmap/config/'.$map->code);

        if (is_dir($mapDir)) {
            system('rm -rf '.$mapDir);
        }

        return ['nuked' => true];
    }

    public function check(MapArea $area)
    {
        sleep(1);

        if (!$area->map->isInstalled()) {
            return [
                'success' => false,
                'error' => "Please install {$area->map->name} before its scan areas.",
            ];
        }

        return ['success' => true, 'error' => false];
    }

    public function configure(Map $map, MapArea $area = null) {
        sleep(2);

        if ($area !== null) {
            return $this->configureArea($area);
        } else {
            return $this->configureMap($map);
        }
    }

    protected function configureArea(MapArea $area)
    {
        $path = storage_path('maps/rocketmap/config');
        if (!is_dir($path)) {
            return [
                'success' => false,
                'error' => "Config directory doesn't exist. Is RocketMap installed?",
            ];
        }

        $path .= "/{$area->map->code}/{$area->slug}.ini";

        $config = view('config.rocketmap.scanner')->with([
            'config' => Setting::first(),
            'area' => $area,
        ]);

        return [
            'success' => false !== file_put_contents($path, $config),
        ];
    }

    protected function configureMap(Map $map)
    {
        $path = storage_path("maps/rocketmap/config");

        if (!is_dir($path)) {
            return [
                'written' => false,
                'errors' => [ "Config directory doesn't exist. Is RocketMap installed?" ],
            ];
        }

        if (!is_dir($path = $path.'/'.$map->code)) {
            system('mkdir '.$path);
        }

        if (\DB::statement("CREATE DATABASE IF NOT EXISTS `{$map->db_name}`")) {
            $config = view('config.rocketmap.server')->with([
                'config' => Setting::first(),
                'map' => $map,
            ]);
        }

        return [
            'written' => false !== file_put_contents($path.'.ini', $config)
        ];
    }

    public function writeAccounts(MapArea $area)
    {
        $accounts = '';

        foreach ($area->accounts as $account) {
            $accounts .= sprintf(
                'ptc,%s,%s'.PHP_EOL,
                $account->username,
                $account->password
            );
        }

        $path = storage_path("maps/rocketmap/config/{$area->map->code}/{$area->slug}.csv");

        return [
            'success' => false !== file_put_contents($path, $accounts),
        ];
    }

    public function start(Map $map)
    {
        $pids = $map->getPids();

        if (count($pids)) {
            return ['running' => true];
        }

        $mapDir = storage_path("maps/rocketmap");

        $cmd_parts = [
            "cd {$mapDir} &&",
            "tmux new-session -s \"tlm_{$map->code}\" -d",
            "python2 runserver.py -cf \"config/{$map->code}.ini\" 2>&1",
        ];

        system(implode(' ', $cmd_parts));
    }

    public function stop(Map $map)
    {
        $pids = $map->getPids();

        if (!count($pids)) {
            return ['stopped' => true];
        }

        $mapDir = storage_path('maps/rocketmap');

        foreach ($pids as $pid) {
            system(sprintf("kill -15 %s", $pid));
        }

        sleep(1);

        return ['stopped' => true];
    }

    public function restart(Map $map)
    {
        $this->stop($map);

        sleep(1);

        $this->start($map);

        return ['started' => (bool) $map->getPids()];
    }
}

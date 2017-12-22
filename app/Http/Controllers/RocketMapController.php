<?php

namespace Twinleaf\Http\Controllers;

use Twinleaf\Map;
use Twinleaf\MapArea;
use Twinleaf\Proxy;
use Twinleaf\Setting;

class RocketMapController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->config = Setting::first();
    }

    public function download()
    {
        $output = [];
        $sudo = 'sudo -Hu twinleaf';
        $mapRoot = storage_path('maps');
        $mapDir = $mapRoot.'/rocketmap';
        $checkFile = $mapDir.'/.twinleaf_downloaded';

        if (file_exists($checkFile)) {
            return ['downloaded' => true];
        } elseif (!is_dir($mapRoot)) {
            exec("{$sudo} mkdir -pv {$mapRoot} 2>&1", $output);
        } elseif (is_dir($mapDir)) {
            exec("{$sudo} rm -rvf {$mapDir} 2>&1", $output);
        }

        $repo = $this->config->map_repo;
        $branch = $this->config->map_branch;

        exec(
            "{$sudo} git clone {$repo} {$mapDir} 2>&1 && cd {$mapDir} && ".
            "{$sudo} git checkout {$branch} 2>&1 && {$sudo} touch {$checkFile}",
            $output
        );

        return [
            'downloaded' => file_exists($checkFile),
            'output' => $output ?? null,
        ];
    }

    public function install()
    {
        $sudo = 'sudo -Hu twinleaf';
        $dir = storage_path('maps/rocketmap');
        $checkFile = $dir.'/.twinleaf_installed';

        if (!file_exists($checkFile)) {
            $output = ["Setting directory permissions..."];
            exec("cd {$dir} && {$sudo} chmod -v 770 config geofences 2>&1", $output);

            $output[] = "Creating new Python virtual environment...";
            exec("{$sudo} virtualenv {$dir}", $output);

            $pip = $this->config->pip_command;
            $output[] = "Using {$pip} to install in {$dir}...";
            exec("cd {$dir} && {$sudo} {$pip} install -r requirements.txt 2>&1", $output);

            $output[] = "Completed pip install!";
            exec(
                "cd {$dir} && {$sudo} npm install 2>&1 && echo 'Completed npm install!' && ".
                "{$sudo} npm run build 2>&1 && {$sudo} touch {$checkFile}",
                $output
            );
        }

        return [
            'installed' => file_exists($checkFile),
            'output' => $output ?? null,
        ];
    }

    public function clean(Map $map) {
        usleep(800000);
        $mapDir = storage_path('maps/rocketmap/config/'.$map->code);

        if (is_dir($mapDir)) {
            system('sudo -u twinleaf rm -rf '.$mapDir);
        }

        return ['nuked' => true];
    }

    public function check(MapArea $area)
    {
        sleep(1);

        if (!$area->map->isInstalled()) {
            $error = "Please install {$area->map->name} before its scan areas.";
        } elseif (!$area->accounts()->count()) {
            $error = "Area {$area->name} needs accounts to run.";
        }

        return [
            'success' => !isset($error),
            'error' => $error ?? null,
        ];
    }

    public function configure(Map $map, MapArea $area = null) {
        usleep(1.5 * 1000000);

        if ($area !== null) {
            return $this->configureArea($area);
        } else {
            return $this->configureMap($map);
        }
    }

    protected function configureArea(MapArea $area)
    {
        if (!is_dir($path = storage_path('maps/rocketmap/config'))) {
            return [
                'success' => false,
                'error' => "Config directory doesn't exist. Is RocketMap installed?",
            ];
        }

        return [
            'success' => false !== file_put_contents(
                "{$path}/{$area->map->code}/{$area->slug}.ini",
                $area->makeConfigFile()
            ),
        ];
    }

    protected function configureMap(Map $map)
    {
        $path = storage_path("maps/rocketmap/config");

        if (!is_dir($path)) {
            return [
                'success' => false,
                'errors' => [ "Config directory doesn't exist. Is RocketMap installed?" ],
            ];
        }

        if (!is_dir($path = $path.'/'.$map->code)) {
            system("sudo -u twinleaf mkdir {$path} && sudo -u twinleaf chmod 770 {$path}");
        }

        if (\DB::statement("CREATE DATABASE IF NOT EXISTS `{$map->db_name}`")) {
            try {
                $config = view('config.rocketmap.server')->with([
                    'config' => $this->config,
                    'map' => $map,
                ])->render();
            } catch (\ErrorException $e) {
                return [
                    'success' => false,
                    'errors' => [$e->getMessage()],
                ];
            }
        }

        return [
            'success' => false !== file_put_contents($path.'.ini', $config),
            'errors' => [],
        ];
    }

    public function writeAccounts(MapArea $area)
    {
        $accounts = $area->accountsToCsv();

        if (empty(trim($accounts))) {
            // It's not technically a success, but we don't want
            // to overwrite any accounts with an empty file, and
            // there might still be proxies to update after this
            return ['success' => true];
        }

        $path = storage_path("maps/rocketmap/config/{$area->map->code}/{$area->slug}.csv");

        return [
            'success' => false !== file_put_contents($path, $accounts),
        ];
    }

    public function writeProxies(MapArea $area)
    {
        $current = $area->proxies->count();
        $target = $area->proxy_target;

        if ($current < $target) {
            $extras = Proxy::available()->unbanned()->limit($target - $current)->get();

            $area->proxies()->saveMany($extras);
            $area->save();
        }

        $proxies = $area->proxiesToCsv();

        $path = storage_path("maps/rocketmap/config/{$area->map->code}/{$area->slug}.txt");

        return [
            'success' => false !== file_put_contents($path, $proxies),
        ];
    }

    public function start($target)
    {
        $result = $target->start();

        if (is_null($result)) {
            return ['running' => true];
        }

        if ($result) {
            $target->writeLog('start');
        }

        return ['running' => $result];
    }

    public function startMap(Map $map)
    {
        return $this->start($map);
    }

    public function startArea(Map $map, MapArea $area)
    {
        return $this->start($area);
    }

    public function stop($target)
    {
        if ($result = $target->stop()) {
            $target->writeLog('stop');
        }

        return ['stopped' => $result];
    }

    public function stopMap(Map $map)
    {
        return $this->stop($map);
    }

    public function stopArea(Map $map, MapArea $area)
    {
        return $this->stop($area);
    }

    public function restart($target)
    {
        if ($target->stop() && $target->start()) {
            $target->writeLog('restart');
        }

        return ['started' => (bool) $target->getPids()];
    }

    public function restartMap(Map $map)
    {
        return $this->restart($map);
    }

    public function restartArea(Map $map, MapArea $area)
    {
        return $this->restart($area, true);
    }
}

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
        $mapRoot = storage_path('maps');
        $mapDir = $mapRoot.'/rocketmap';
        $checkFile = $mapDir.'/.twinleaf_downloaded';

        if (file_exists($checkFile)) {
            return ['downloaded' => true];
        } elseif (!is_dir($mapRoot)) {
            exec("mkdir -pv {$mapRoot} 2>&1", $output);
        } elseif (is_dir($mapDir)) {
            exec("rm -rvf {$mapDir} 2>&1", $output);
        }

        $repo = $this->config->map_repo;
        $branch = $this->config->map_branch;

        exec("git clone {$repo} {$mapDir} 2>&1 && cd {$mapDir} && git checkout {$branch} 2>&1 && touch {$checkFile}", $output);

        return [
            'downloaded' => file_exists($checkFile),
            'output' => $output ?? null,
        ];
    }

    public function install()
    {
        $dir = storage_path('maps/rocketmap');
        $checkFile = $dir.'/.twinleaf_installed';

        if (!file_exists($checkFile)) {
            $pip = $this->config->pip_command;
            $npm = 'sudo -H npm';

            $output = ["Using {$pip} to install in {$dir}..."];
            exec("cd {$dir} && {$pip} install -r requirements.txt 2>&1", $output);

            $output[] = "Completed pip install!";
            exec("cd {$dir} && {$npm} install 2>&1 && echo 'Completed npm install!' && {$npm} run build 2>&1 && touch {$checkFile}", $output);
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
            'config' => $this->config,
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
            try {
                $config = view('config.rocketmap.server')->with([
                    'config' => $this->config,
                    'map' => $map,
                ])->render();
            } catch (\ErrorException $e) {
                return [
                    'written' => false,
                    'errors' => [$e->getMessage()],
                ];
            }
        }

        return [
            'written' => false !== file_put_contents($path.'.ini', $config),
            'errors' => [],
        ];
    }

    public function writeAccounts(MapArea $area)
    {
        $accounts = '';

        foreach ($area->accounts as $account) {
            if ($account->activated_at === null) {
                continue;
            }

            $accounts .= $account->format().PHP_EOL;
        }

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

        $proxies = '';

        foreach ($area->proxies() as $proxy) {
            $proxies .= $proxy->url.PHP_EOL;
        }

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

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
        $mapRoot = storage_path('maps');
        $mapDir = $mapRoot.'/rocketmap';
        $checkFile = $mapDir.'/.twinleaf_downloaded';

        if (file_exists($checkFile)) {
            return ['downloaded' => true];
        } elseif (!is_dir($mapRoot)) {
            exec('mkdir -p '.$mapRoot);
        } elseif (is_dir($mapDir)) {
            exec('rm -rf '.$mapDir);
        }

        $repo = $this->config->map_repo;
        $branch = $this->config->map_branch;

        exec("git clone {$repo} {$mapDir} && cd {$mapDir} && git checkout {$branch} && touch {$checkFile}");

        return ['downloaded' => file_exists($checkFile)];
    }

    public function install()
    {
        $dir = storage_path('maps/rocketmap');
        $checkFile = $dir.'/.twinleaf_installed';

        if (!file_exists($checkFile)) {
            $pip = $this->config->pip_command;
            $npm = 'sudo -H npm';

            exec("cd {$dir} && {$pip} install -r requirements.txt");
            exec("cd {$dir} && {$npm} install && {$npm} run build && touch {$checkFile}");
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
            $config = view('config.rocketmap.server')->with([
                'config' => $this->config,
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
            $accounts .= $account->format().PHP_EOL;
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

        foreach ($area->proxies as $proxy) {
            $proxies .= $proxy->url.PHP_EOL;
        }

        $path = storage_path("maps/rocketmap/config/{$area->map->code}/{$area->slug}.txt");

        return [
            'success' => false !== file_put_contents($path, $proxies),
        ];
    }

    public function start($target, $isArea = false)
    {
        $pids = $target->getPids();

        if (count($pids)) {
            return ['running' => true];
        }

        $mapDir = storage_path('maps/rocketmap');
        $tmuxId = $isArea ? 'tla_'.$target->slug : 'tlm_'.$target->code;
        $config = $isArea ? "{$target->map->code}/{$target->slug}" : $target->code;
        $python = $this->config->python_command;

        $cmd_parts = [
            "cd {$mapDir} &&",
            "tmux new-session -s \"{$tmuxId}\" -d",
            "{$python} runserver.py -cf \"config/{$config}.ini\" 2>&1",
        ];

        system(implode(' ', $cmd_parts));

        $target->applyUptimeMax()->setStartTime()->save();
    }

    public function startMap(Map $map)
    {
        return $this->start($map);
    }

    public function startArea(MapArea $area)
    {
        return $this->start($area, true);
    }

    public function stop($target)
    {
        $pids = $target->getPids();

        if (!count($pids)) {
            return ['stopped' => true];
        }

        foreach ($pids as $pid) {
            system(sprintf("kill -15 %s", $pid));
        }

        $target->applyUptimeMax()->unsetStartTime()->save();

        sleep(1);

        return ['stopped' => true];
    }

    public function stopMap(Map $map)
    {
        return $this->stop($map);
    }

    public function stopArea(MapArea $area)
    {
        return $this->stop($area);
    }

    public function restart($target, $isArea = false)
    {
        $this->stop($target);

        sleep(1);

        $this->start($target, $isArea);

        return ['started' => (bool) $target->getPids()];
    }

    public function restartMap(Map $map)
    {
        return $this->restart($map);
    }

    public function restartArea(MapArea $area)
    {
        return $this->restart($area, true);
    }
}

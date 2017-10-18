<?php

namespace Twinleaf\Http\Controllers;

use Twinleaf\Map;
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

    public function configure(Map $map) {
        sleep(2);

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

        $config = view('config.rocketmap.server')->with([
            'config' => Setting::first(),
            'map' => $map,
        ]);

        return [
            'written' => false !== file_put_contents($path.'.ini', $config)
        ];
    }
}

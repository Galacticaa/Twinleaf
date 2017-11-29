<?php

use Illuminate\Database\Seeder;
use Twinleaf\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $bin = storage_path('maps/rocketmap/bin');

        $config = new Setting;
        $config->map_repo = 'https://github.com/RocketMap/RocketMap.git';
        $config->map_branch = 'develop';
        $config->python_command = $bin.'/python';
        $config->pip_command = $bin.'/pip';
        $config->save();
    }
}

<?php

namespace Twinleaf\Services;

use Twinleaf\Account;
use Twinleaf\Setting;
use Twinleaf\Proxy;

class KinanCore
{
    protected $basePath;

    protected $binaryPath;

    protected $configPath;

    public function __construct()
    {
        $base = $this->basePath = base_path('bin/kinan');
        $this->binaryPath = $base.'/KinanCity-core.jar';
        $this->configPath = $base.'/config.properties';
    }

    public function isInstalled()
    {
        return file_exists($this->binaryPath) && file_exists($this->configPath);
    }

    public function isRunning()
    {
        return 0 < count($this->getPids());
    }

    public function configure()
    {
        $config = view('config.kinan')->with([
            'config' => Setting::first(),
            'activateProxy' => Proxy::whereForActivation(true)->first(),
            'database' => (object) config('database.connections.mysql'),
        ]);

        return false !== file_put_contents($this->configPath, $config);
    }

    public function start()
    {
        if (!$this->writeAccountsFile()) {
            return;
        }

        $options = '-t 15';

        if (Setting::first()->disable_proxy_check) {
            $options .= ' -npc';
        }

        $cmd_parts = [
            "cd {$this->basePath} &&",
            "tmux new-session -s tls_creator -d",
            "java -jar KinanCity-core.jar -a accounts.csv {$options} 2>&1",
        ];

        system(implode(' ', $cmd_parts));
    }

    protected function writeAccountsFile() {
        $accounts = Account::unregistered()->get();
        $csv = '#username;email;password;dob;country'.PHP_EOL;

        if ($accounts->isEmpty()) {
            return false;
        }

        foreach ($accounts as $account) {
            $csv .= $account->format('kinan').PHP_EOL;
        }

        return false !== file_put_contents($this->basePath.'/accounts.csv', $csv);
    }

    public function getPids()
    {
        $cmd_parts = [
            'ps axf | grep "KinanCity-core.jar" |',
            "grep -v grep | awk '{ print \$1 }'",
        ];

        exec(implode(' ', $cmd_parts), $pids);

        return $pids;
    }
}

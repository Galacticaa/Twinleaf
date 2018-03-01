<?php

namespace Twinleaf\Services;

class KinanMail
{
    protected $basePath;

    protected $binaryPath;

    protected $configPath;

    public function __construct()
    {
        $base = $this->basePath = base_path('bin/kinan');
        $this->binaryPath = $base.'/KinanCity-mail.jar';
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

    public function start()
    {
        $cmd_parts = [
            "cd {$this->basePath} &&",
            "tmux new-session -s tls_activator -d",
            "sudo java -jar KinanCity-mail.jar 2>&1",
        ];

        system(implode(' ', $cmd_parts));
    }

    public function getPids()
    {
        $cmd_parts = [
            'ps axf | grep "KinanCity-mail.jar" |',
            "grep -v grep | awk '{ print \$1 }'",
        ];

        exec(implode(' ', $cmd_parts), $pids);

        return $pids;
    }
}

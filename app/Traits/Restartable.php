<?php

namespace Twinleaf\Traits;

use Carbon\Carbon;
use Twinleaf\Setting;

Trait Restartable
{
    protected $pids = [];

    public function isUp($force = false)
    {
        return 0 < count($this->getPids($force));
    }

    public function isDown($force = false)
    {
        return !$this->isUp($force);
    }

    public function start()
    {
        if ($this->isUp()) {
            return null;
        }

        $cmd = sprintf(
            "cd %s && tmux new-session -s \"%s\" -d %s runserver.py -cf \"%s\" 2>&1",
            storage_path('maps/rocketmap'),
            $this->getSessionName(),
            Setting::first()->python_command,
            $this->getConfigFile()
        );

        for ($i = 0; $i < 3 && $this->isDown(true); $i++) {
            system($cmd);

            sleep (1);
        }

        // It's possible that RM can appear to start but later fail,
        // for example if there are no accounts or valid proxies.
        // Make sure it's still running after a few seconds.
        sleep(5);

        if ($this->isUp(true)) {
            $this->applyUptimeMax()->setStartTime()->save();
        }

        return $this->isUp();
    }

    public function stop()
    {
        for ($i = 0; $i < 3; $i++) {
            $pids = $this->getPids(true);

            if ($this->isDown()) {
                break;
            }

            foreach ($pids as $pid) {
                system('kill -15 '.$pid);
            }

            sleep(2);
        }

        if ($this->isDown()) {
            $this->applyUptimeMax()->unsetStartTime()->save();
        }

        return $this->isDown();
    }

    public function setStartTime()
    {
        $this->started_at = Carbon::now();

        return $this;
    }

    public function unsetStartTime()
    {
        $this->started_at = null;

        return $this;
    }

    public function getUptimeAttribute()
    {
        return $this->started_at === null ? 0
             : $this->started_at->diffInSeconds();
    }

    public function getHumanUptimeAttribute()
    {
        return $this->started_at === null ? '---'
             : $this->started_at->diffForHumans(null, true);
    }

    public function applyUptimeMax()
    {
        $uptime = $this->uptime;

        if ($uptime > $this->uptime_max) {
            $this->uptime_max = $uptime;
        }

        return $this;
    }

    public function getHumanUptimeMaxAttribute()
    {
        return !$this->uptime_max ? '---'
             : Carbon::now()->addSeconds($this->uptime_max)->diffForHumans(null, true);
    }

    public function getPids($force = false)
    {
        $ps = "ps axf | grep runserver.py | grep -v grep | grep -v tmux | grep '%s' | awk '{ print \$1 }'";

        if ($force || empty($this->pids)) {
            exec(sprintf($ps, $this->getPidFilter()), $pids);

            $this->pids = $pids;
        }

        return $this->pids;
    }
}

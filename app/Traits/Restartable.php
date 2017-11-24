<?php

namespace Twinleaf\Traits;

use Carbon\Carbon;

Trait Restartable
{
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
}

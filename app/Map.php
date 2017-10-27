<?php

namespace Twinleaf;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
    protected $dates = [
        'created_at',
        'updated_at',
        'started_at',
    ];

    protected $fillable = [
        'name',
        'code',
        'url',
        'location',
        'db_name',
        'db_user',
        'db_pass',
    ];

    public function accounts()
    {
        return $this->hasManyThrough(Account::class, MapArea::class);
    }

    public function areas()
    {
        return $this->hasMany(MapArea::class);
    }

    public function proxies()
    {
        return $this->hasManyThrough(Proxy::class, MapArea::class);
    }

    public function isInstalled()
    {
        $map = storage_path('maps/rocketmap/.twinleaf_installed');
        $config = storage_path("maps/rocketmap/config/{$this->code}.ini");

        return file_exists($map) && file_exists($config);
    }

    public function isUp()
    {
        return 0 < count($this->getPids());
    }

    public function isDown()
    {
        return !$this->isUp();
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

    public function getRouteKeyName()
    {
        return 'code';
    }

    public function getPids()
    {
        $cmd_parts = [
            "ps axf | grep runserver.py | grep -v grep |",
            "grep -v tmux | grep {$this->code}.ini | awk '{ print \$1 }'",
        ];

        exec(implode(' ', $cmd_parts), $pids);

        return $pids;
    }
}

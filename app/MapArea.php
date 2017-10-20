<?php

namespace Twinleaf;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MapArea extends Model
{
    protected $dates = [
        'created_at',
        'updated_at',
        'started_at',
    ];

    protected $fillable = [
        'name',
        'slug',
        'location',
        'map_id'
    ];

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function isInstalled()
    {
        $rocket = storage_path('maps/rocketmap/.twinleaf_installed');
        $parent = storage_path("maps/rocketmap/config/{$this->map->code}.ini");
        $config = storage_path("maps/rocketmap/config/{$this->map->code}/{$this->slug}.ini");

        return file_exists($rocket) && file_exists($parent) && file_exists($config);
    }

    public function isUp()
    {
        return 0 < count($this->getPids());
    }

    public function isDown()
    {
        return !$this->isUp();
    }

    public function map()
    {
        return $this->belongsTo(Map::class);
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
        return 'slug';
    }

    public function getPids()
    {
        $cmd_parts = [
            "ps axf | grep runserver.py | grep -v grep |",
            "grep -v tmux | grep {$this->slug} | awk '{ print \$1 }'",
        ];

        exec(implode(' ', $cmd_parts), $pids);

        return $pids;
    }
}

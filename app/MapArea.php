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
        'map_id',
        'location',
        'accounts_target',
        'proxy_target',
    ];

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function proxies()
    {
        return $this->hasMany(Proxy::class);
    }

    public function scopeDueUpdate($query)
    {
        return $query->where(function ($q) {
            $interval = Carbon::now()->subMinutes(30);

            $q->whereNull('last_restart')->orWhere('last_restart', '<', $interval);
        });
    }

    public function scopeWithActivatedAccounts($query)
    {
        return $query->with('accounts', function ($q) {
            $q->activated()->orderBy('activated_at', 'desc');
        });
    }

    public function isInstalled()
    {
        $files = [
            "maps/rocketmap/.twinleaf_installed",
            "maps/rocketmap/config/{$this->map->code}.ini",
            "maps/rocketmap/config/{$this->map->code}/{$this->slug}.ini",
            "maps/rocketmap/config/{$this->map->code}/{$this->slug}.txt",
            "maps/rocketmap/config/{$this->map->code}/{$this->slug}.csv",
        ];

        foreach ($files as $file) {
            if (!file_exists(storage_path($file))) {
                return false;
            }
        }

        return true;
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

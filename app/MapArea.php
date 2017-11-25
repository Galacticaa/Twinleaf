<?php

namespace Twinleaf;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MapArea extends Model
{
    use Traits\Restartable;

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
        'radius',
        'accounts_target',
        'proxy_target',
        'db_threads',
        'speed_scan',
        'beehive',
        'workers',
        'workers_per_hive',
        'scan_duration',
        'rest_interval',
        'max_empty',
        'max_failures',
        'max_retries',
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

            $q->whereNull('started_at')->orWhere('started_at', '<', $interval);
        });
    }

    public function scopeWithActivatedAccounts($query)
    {
        return $query->with(['accounts' => function ($q) {
            $q->activated()->orderBy('activated_at', 'desc');
        }]);
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

    public function map()
    {
        return $this->belongsTo(Map::class);
    }

    public function getConfigFile()
    {
        return 'config/'.$this->map->code.'/'.$this->slug.'.ini';
    }

    public function getSessionName()
    {
        return 'tla_'.$this->slug;
    }

    /**
     * Define the value used to filter running processes
     *
     * @return string
     */
    public function getPidFilter()
    {
        return $this->slug;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}

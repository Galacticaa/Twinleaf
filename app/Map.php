<?php

namespace Twinleaf;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
    use Traits\Restartable;

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
        'analytics_key',
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

    public function getConfigFile()
    {
        return 'config/'.$this->code.'.ini';
    }

    public function getSessionName()
    {
        return 'tlm_'.$this->code;
    }

    /**
     * Define the value used to filter running processes
     *
     * @return string
     */
    public function getPidFilter()
    {
        return $this->code.'.ini';
    }

    public function getRouteKeyName()
    {
        return 'code';
    }

    public function url($route = 'show')
    {
        return route('maps.'.$route, [
            'map' => $this,
        ]);
    }
}

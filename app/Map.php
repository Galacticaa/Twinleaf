<?php

namespace Twinleaf;

use Exception;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
    use Traits\Loggable;
    use Traits\Restartable;

    protected $dates = [
        'created_at',
        'updated_at',
        'started_at',
    ];

    protected $fillable = [
        'name',
        'code',
        'description',
        'image_url',
        'url',
        'location',
        'analytics_key',
        'db_name',
        'db_user',
        'db_pass',
    ];

    protected $logType = 'map';

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

    public function hasLatestConfig()
    {
        if (!$this->isInstalled()) {
            throw new Exception("The map must be installed first.");
        }

        $old = file_get_contents(storage_path("maps/rocketmap/config/{$this->code}.ini"));
        $new = view('config.rocketmap.server')->with([
            'config' => Setting::first(),
            'map' => $this,
        ])->render();

        return $old === $new;
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

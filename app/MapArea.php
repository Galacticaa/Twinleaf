<?php

namespace Twinleaf;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MapArea extends Model
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

    protected $logType = 'map_area';

    /**
     * @var array  Location represented as an array
     */
    protected $locationArray;

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function accountsToCsv()
    {
        $csv = '';
        $accounts = $this->accounts()->activated()->get();

        foreach ($accounts as $account) {
            $csv .= $account->format().PHP_EOL;
        }

        return $csv;
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
            $q->activated();
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

    public function setGeofenceAttribute($value)
    {
        if (!trim($value)) {
            $this->attributes['geofence'] = null;

            return;
        }

        $lines = explode("\n", trim($value));
        $coords = [];

        foreach ($lines as $latlng) {
            if (empty(trim($latlng))) {
                continue;
            }

            list($lat, $lng) = explode(',', trim($latlng));

            $coords[] = (object) compact('lat', 'lng');
        }

        $this->attributes['geofence'] = json_encode($coords, JSON_NUMERIC_CHECK);
    }

    public function getGeofenceStringAttribute()
    {
        if (is_null($this->geofence)) {
            return null;
        }

        $coords = json_decode($this->geofence);
        $fence = '[Geofence]'.PHP_EOL;

        foreach ($coords as $marker) {
            $fence .= $marker->lat.','.$marker->lng.PHP_EOL;
        }

        return $fence;
    }

    public function writeGeofenceFile()
    {
        $file = storage_path(sprintf(
            'maps/rocketmap/geofences/%s_%s.csv',
            $this->map->code,
            $this->slug
        ));

        $fenceNow = file_exists($file) ? file_get_contents($file) : null;

        if ($fenceNow === $this->geofenceString) {
            return;
        }

        if (empty($this->geofenceString)) {
            return unlink($file);
        }

        return file_put_contents($file, $this->geofenceString);
    }

    public function getLatAttribute()
    {
        return $this->locationToArray()['lat'];
    }

    public function getLngAttribute()
    {
        return $this->locationToArray()['lng'];
    }

    public function locationToArray()
    {
        if (empty($this->locationArray)) {
            list($lat, $lng) = explode(',', $this->location);

            $this->locationArray = compact('lat', 'lng');
        }

        return $this->locationArray;
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

    public function url($route = 'show')
    {
        return route('maps.areas.'.$route, [
            'map' => $this->map,
            'area' => $this,
        ]);
    }
}

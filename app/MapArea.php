<?php

namespace Twinleaf;

use Illuminate\Database\Eloquent\Model;

class MapArea extends Model
{
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

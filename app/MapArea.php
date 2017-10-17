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

    public function map()
    {
        return $this->belongsTo(Map::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}

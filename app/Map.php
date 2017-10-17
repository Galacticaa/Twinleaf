<?php

namespace Twinleaf;

use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
    protected $fillable = [
        'name',
        'code',
        'url',
        'location',
        'db_name',
        'db_user',
        'db_pass',
    ];

    public function areas()
    {
        return $this->hasMany(MapArea::class);
    }

    public function getRouteKeyName()
    {
        return 'code';
    }
}

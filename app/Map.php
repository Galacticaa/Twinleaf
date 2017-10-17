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

    public function accounts()
    {
        return $this->hasManyThrough(Account::class, MapArea::class);
    }

    public function areas()
    {
        return $this->hasMany(MapArea::class);
    }

    public function getRouteKeyName()
    {
        return 'code';
    }
}

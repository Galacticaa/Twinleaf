<?php

namespace Twinleaf;

use Illuminate\Database\Eloquent\Model;

class Proxy extends Model
{
    protected $fillable = [
        'url',
    ];

    public function area()
    {
        return $this->belongsTo(MapArea::class, 'map_area_id');
    }

    public function scopeAvailable($query)
    {
        return $query->whereNull('map_area_id');
    }

    public function scopeNoPogoBan($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('pogo_ban');
            $q->orWhere('pogo_ban', '=', false);
        });
    }

    public function scopeNoPtcBan($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('ptc_ban');
            $q->orWhere('ptc_ban', '=', false);
        });
    }

    public function scopeUnbanned($query)
    {
        return $query->noPogoBan()->noPtcBan();
    }
}

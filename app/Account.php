<?php

namespace Twinleaf;

use Twinleaf\Accounts\Generator;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'name',
        'email',
    ];

    protected $dates = [
        'registered_at',
        'activated_at',
        'created_at',
        'updated_at',
    ];

    public function area()
    {
        return $this->belongsTo('Twinleaf\MapArea', 'map_area_id');
    }

    public function getRouteKeyName()
    {
        return 'username';
    }

    public function setDomainAttribute($value)
    {
        $this->domain = $value;
    }

    public function setUsernameAttribute($username)
    {
        $this->attributes['username'] = $username;
        $this->attributes['email'] = $username.'@'.$this->domain;
    }

    public function replace()
    {
        $replacement = (new Generator($this->area))->generateSingle();

        $this->area()->dissociate();
        $this->save();

        return $replacement;
    }
}

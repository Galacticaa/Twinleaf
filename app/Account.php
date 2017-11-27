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

    public function scopeActivated($query)
    {
        return $query->whereNotNull('activated_at');
    }

    public function scopeUnregistered($query)
    {
        return $query->whereNull('registered_at')->whereNull('activated_at');
    }

    public function format($format = 'rocketmap')
    {
        if (!in_array($format, ['kinan', 'rocketmap'])) {
            throw new \Exception("Invalid format '{$format}'");
        }

        if ($format == 'rocketmap') {
            $separator = ',';
            $values = [
                'ptc',
                $this->attributes['username'],
                $this->attributes['password'],
            ];
        } else {
            $separator = ';';
            $values = [
                $this->attributes['username'],
                $this->attributes['email'],
                $this->attributes['password'],
                $this->attributes['birthday'],
                $this->attributes['country']
            ];
        }

        return implode($separator, $values);
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
        $area = $this->area;
        $replacement = (new Generator($this->area))->generateSingle();

        $this->area()->dissociate();
        $this->save();

        $area->writeLog('replace', sprintf(
            '<a href="%s">%s</a>\'s account <strong>%s</strong> was replaced with <strong>%s</strong>.',
            $area->url(), $area->name, $this->username, $replacement->username
        ));

        return $replacement;
    }
}

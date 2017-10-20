<?php

namespace Twinleaf;

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
}

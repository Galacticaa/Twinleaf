<?php

namespace Twinleaf;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'name',
        'email',
    ];
}

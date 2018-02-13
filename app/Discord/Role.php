<?php

namespace Twinleaf\Discord;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'discord_roles';

    protected $fillable = ['code', 'discord_id', 'position'];
}

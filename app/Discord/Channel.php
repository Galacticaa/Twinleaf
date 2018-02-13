<?php

namespace Twinleaf\Discord;

use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    protected $table = 'discord_channels';

    protected $fillable = ['code', 'discord_id', 'type', 'position', 'parent_id'];
}

<?php

namespace Twinleaf\Discord;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    protected $table = 'discord_config';

    protected $fillable = ['bot_token', 'guild_id'];
}

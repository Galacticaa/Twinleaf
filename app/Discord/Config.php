<?php

namespace Twinleaf\Discord;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'discord_config';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['bot_token', 'guild_id', 'colours'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'guild_id' => 'integer',
        'colours' => 'array',
    ];

    public function coloursAsInt()
    {
        $colours = $this->colours;

        foreach ($colours as $key => $colour) {
            $colours[$key] = hexdec($colour);
        }

        return $colours;
    }
}

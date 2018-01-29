<?php

namespace Twinleaf\Http\Controllers;

use Twinleaf\Map;
use RestCord\DiscordClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DiscordController extends Controller
{
    /**
     * An instance of the Discord API
     *
     * @var DiscordClient
     */
    protected $discord;

    /**
     * The Guild ID of the Discord server
     *
     * @var integer
     */
    protected $serverId;

    public function __construct()
    {
        $this->discord = new DiscordClient([
            'token' => env('DISCORD_BOT_TOKEN'),
            'logger' => Log::getMonolog(),
        ]);

        $this->serverId = (int) env('DISCORD_SERVER_ID');
    }

    /**
     * Show a list of the Discord server's roles.
     *
     * @return \Illuminate\Http\Response
     */
    public function clean()
    {
        $channels = $this->guild()->getGuildChannels(['guild.id' => $this->serverId]);
        $roles = $this->guild()->getGuildRoles(['guild.id' => $this->serverId]);

        return view('discord.clean', [
            'channels' => collect($channels)->sortBy('position'),
            'roles' => collect($roles)->sortByDesc('position'),
        ]);
    }

    /**
     * Delete requested roles from Discord
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function purge(Request $request)
    {
        $roles = $request->input('roles');

        foreach ($roles as $role) {
            $response = $this->guild()->deleteGuildRole([
                'guild.id' => $this->serverId,
                'role.id' => $role,
            ]);
        }

        return redirect()->back();
    }

    /**
     * Access the Discord Guild API
     *
     * @return \GuzzleHttp\Client
     */
    protected function guild()
    {
        return $this->discord->guild;
    }
}

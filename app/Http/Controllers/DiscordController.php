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
    public function cleanup()
    {
        $channels = collect($this->guild()->getGuildChannels([
            'guild.id' => $this->serverId
        ]))->sortBy(function ($channel, $key) {
            return $channel['type'] . str_pad(
                $channel['position'], 5, '0', STR_PAD_LEFT
            );
        })->keyBy('id');

        $channels->transform(function ($channel) {
            return (object) $channel;
        });

        $categories = $channels->where('type', '=', 4);

        $categories->transform(function ($category, $id) use ($channels) {
            $category->channels = $channels->where('parent_id', '=', $id);

            return (object) $category;
        });

        $roles = $this->guild()->getGuildRoles(['guild.id' => $this->serverId]);

        return view('discord.clean', [
            'categories' => $categories,
            'roles' => collect($roles)->sortByDesc('position'),
        ]);
    }

    /**
     * Delete requested roles from Discord
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function cleanRoles(Request $request)
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
     * Delete requested channels from Discord
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function cleanChannels(Request $request)
    {
        $channels = $request->input('channels');

        foreach ($channels as $channel) {
            $response = $this->discord->channel->deletecloseChannel([
                'channel.id' => (int) $channel,
            ]);

            if ($response['code'] && $response['message']) {
                return redirect()->back()->withErrors([
                    'channels' => "<strong>Error #{$response['code']}</strong> - {$response['message']}",
                ])->withInput();
            }
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

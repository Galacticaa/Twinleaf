<?php

namespace Twinleaf\Http\Controllers;

use Twinleaf\Map;
use Twinleaf\Discord;
use Illuminate\Http\Request;

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
    protected $guildId;

    public function __construct()
    {
        $this->discord = new Discord;
        $this->guildId = $this->discord->getGuildId();
    }

    /**
     * Show a list of the Discord server's roles.
     *
     * @return \Illuminate\Http\Response
     */
    public function cleanup()
    {
        $channels = collect($this->guild()->getGuildChannels([
            'guild.id' => $this->guildId
        ]))->sortBy(function ($channel, $key) {
            return $channel->type . str_pad(
                $channel->position, 5, '0', STR_PAD_LEFT
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

        $roles = $this->guild()->getGuildRoles(['guild.id' => $this->guildId]);

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
                'guild.id' => $this->guildId,
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
            try {
                $response = $this->discord->channel->deleteOrcloseChannel([
                    'channel.id' => (int) $channel,
                ]);
            } catch (\GuzzleHttp\Command\Exception\CommandClientException $e) {
                $response = $e->getResponse();
                $code = $response->getStatusCode();
                $message = $response->getReasonPhrase();

                return redirect()->back()->withErrors([
                    'channels' => "<strong>Error #{$code}</strong> - {$message}",
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

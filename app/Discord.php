<?php

namespace Twinleaf;

use Twinleaf\Discord\Config;
use RestCord\DiscordClient;
use Illuminate\Support\Facades\Log;

class Discord
{
    protected $client;

    protected $config;

    public function __construct()
    {
        try {
            $this->config = Config::first();
        } catch (\Exception $e) {
            return;
        }

        $this->client = new DiscordClient([
            'token' => $this->config->bot_token ?? 'none',
            'logger' => Log::getMonolog(),
        ]);
    }

    public function getGuildId()
    {
        return $this->config->guild_id ?? null;
    }

    public function __call($method, array $args)
    {
        return $this->client->$method(...$args);
    }

    public function __get($arg)
    {
        return $this->client->$arg;
    }
}

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
        $this->config = Config::first();

        $this->client = new DiscordClient([
            'token' => $this->config->bot_token,
            'logger' => Log::getMonolog(),
        ]);
    }

    public function getGuildId()
    {
        return $this->config->guild_id;
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

<?php

namespace Twinleaf\Console\Commands;

use Twinleaf\MapArea;
use Twinleaf\Discord\Role;
use RestCord\DiscordClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DiscordPrep extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discord:prepare';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prepare Discord with the relevant roles.';

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

    /**
     * Collection of Map Areas
     *
     * @var Collection
     */
    protected $areas;

    /**
     * List of teams in the game
     *
     * @var array
     */
    protected $teams = ['mystic', 'instinct', 'valor'];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->discord = new DiscordClient([
            'token' => env('DISCORD_BOT_TOKEN'),
            'logger' => Log::getMonolog(),
        ]);

        $this->areas = MapArea::enabled()->get();
        $this->serverId = (int) env('DISCORD_SERVER_ID');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->processRoles();
    }

    protected function processRoles()
    {
        $roles = $this->getRequiredRoles();
        $rolesLocal = Role::all()->keyBy('code');
        $rolesRemote = $this->loadRemoteRoles();

        foreach ($roles as $code => $name) {
            $dbRole = $rolesLocal[$code] ?? null;

            if ($dbRole && array_key_exists($dbRole->discord_id, $rolesRemote)) {
                $this->line("Role '{$name}' already exists.");
                continue;
            }

            $this->createRole($code, $name);
        }
    }

    protected function getRequiredRoles()
    {
        $names = ['global.champions' => 'Champions'];

        foreach ($this->teams as $team) {
            $names['global.'.$team] = ucfirst($team);
        }

        foreach ($this->areas as $area) {
            $code = 'area.'.$area->slug;
            $names[$code] = $area->name;

            foreach (['mystic', 'instinct', 'valor'] as $team) {
                $names[$code.'.'.$team] = $area->slug.'-'.$team;
            }

            $names[$code.'.spawns'] = $area->slug.'-pokemon';
            $names[$code.'.raids'] = $area->slug.'-raids';
        }

        return $names;
    }

    protected function loadRemoteRoles()
    {
        $roles = collect($this->guild()->getGuildRoles([
            'guild.id' => $this->serverId
        ]))->keyBy('id')->transform(function($item, $key) {
            return (object) $item;
        });

        return $roles->all();
    }

    protected function createRole($code, $name, $createDb = true)
    {
        $this->info("Creating role {$name} with code {$code}");

        $role = $this->guild()->createGuildRole([
            'guild.id' => $this->serverId,
            'name' => $name,
        ]);

        return Role::updateOrCreate(['code' => $code], [
            'discord_id' => $role['id'],
            'position' => $role['position'],
        ]);
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

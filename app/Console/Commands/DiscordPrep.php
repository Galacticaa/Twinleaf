<?php

namespace Twinleaf\Console\Commands;

use Twinleaf\MapArea;
use Twinleaf\Discord\Role;
use Twinleaf\Discord\Channel;
use RestCord\DiscordClient;
use RestCord\Model\Channel\Overwrite;
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
    protected $description = 'Prepare Discord with the relevant roles & channels.';

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
     * Collection of Discord channels
     *
     * @var Collection
     */
    protected $channels;
    protected $roles;

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
        $this->loadRemoteRoles();

        $this->processRoles();

        $this->loadRemoteChannels();

        $this->processCategories();
    }

    protected function processRoles()
    {
        $roles = $this->getRequiredRoles();
        $rolesLocal = Role::all()->keyBy('code');
        $rolesRemote = $this->roles->all();

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

        $this->roles = $roles;
    }

    protected function createRole($code, $name)
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

    protected function processCategories()
    {
        $categories = $this->getRequiredCategories();
        $categoriesLocal = Channel::whereType(4)->get()->keyBy('code');
        $categoriesRemote = $this->channels->where('type', '=', 4);

        foreach ($categories as $code => $category) {
            $dbCat = $categoriesLocal[$code] ?? null;

            if ($dbCat && $categoriesRemote->has($dbCat->discord_id)) {
                $this->line("Category '{$category['name']}' already exists.");
                continue;
            }

            $this->createChannel($code, $category);
        }
    }

    protected function getRequiredCategories()
    {
        $names = [];

        $readOnly = new Overwrite([
            'id' => $this->findRole('@everyone'),
            'type' => 'role',
            'deny' => 1024,
        ]);
        $exceptDevs = new Overwrite([
            'id' => $this->findRole('developers')->id,
            'type' => 'role',
            'allow' => 1024,
        ]);

        foreach ($this->areas as $area) {
            $code = 'area.'.$area->slug;
            $names[$code] = [
                'name' => $area->name,
                'permissions' => [$readOnly, $exceptDevs, new Overwrite([
                    'id' => $this->findRole($area->name)->id,
                    'type' => 'role',
                    'allow' => 1024,
                ])],
            ];

            $names[$code.'.raids'] = [
                'name' => $area->name.' Raidar',
                'permissions' => [$readOnly, $exceptDevs, new Overwrite([
                    'id' => $this->findRole($area->slug.'-raids')->id,
                    'type' => 'role',
                    'allow' => 1024,
                ])],
            ];
            $names[$code.'.spawns'] = [
                'name' => $area->name.' Spawns',
                'permissions' => [$readOnly, $exceptDevs, new Overwrite([
                    'id' => $this->findRole($area->slug.'-pokemon')->id,
                    'type' => 'role',
                    'allow' => 1024,
                ])],
            ];
        }

        return $names;
    }

    protected function findRole($name)
    {
        return $this->roles->where('name', '=', $name)->first();
    }

    protected function loadRemoteChannels()
    {
        $channels = collect($this->guild()->getGuildChannels([
            'guild.id' => $this->serverId
        ]))->sortBy(function ($channel, $key) {
            return $channel->type . str_pad(
                $channel->position, 5, '0', STR_PAD_LEFT
            );
        });

        $this->channels = $channels->keyBy('id');
    }

    protected function createChannel($code, $category, $parent = null)
    {
        $this->info("Creating category {$category['name']} with code {$code}");

        $data = [
            'guild.id' => $this->serverId,
            'name' => $category['name'],
            'type' => 4,
        ];

        if (count($category['permissions'])) {
            $data['permission_overwrites'] = $category['permissions'];
        }

        $category = $this->guild()->createGuildChannel($data);

        return Channel::updateOrCreate(['code' => $code], [
            'discord_id' => $category->id,
            'position' => $category->position,
            'type' => $category->type,
            'parent_id' => 0,
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

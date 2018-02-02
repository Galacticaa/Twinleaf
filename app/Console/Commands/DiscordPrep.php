<?php

namespace Twinleaf\Console\Commands;

use Twinleaf\MapArea;
use Twinleaf\Discord;
use Twinleaf\Discord\Role;
use Twinleaf\Discord\Channel;
use RestCord\Model\Channel\Overwrite;
use Illuminate\Console\Command;

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
    protected $guildId;

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

        $this->areas = MapArea::enabled()->get();

        $this->discord = new Discord;
        $this->guildId = (int) $this->discord->getGuildId();
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
        $this->processChannels();
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
            'guild.id' => $this->guildId
        ]))->keyBy('id')->transform(function($item, $key) {
            return (object) $item;
        });

        $this->roles = $roles;
    }

    protected function createRole($code, $name)
    {
        $this->info("Creating role {$name} with code {$code}");

        $role = $this->guild()->createGuildRole([
            'guild.id' => $this->guildId,
            'name' => $name,
        ]);

        return Role::updateOrCreate(['code' => $code], [
            'discord_id' => $role->id,
            'position' => $role->position,
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

        foreach ($this->areas as $area) {
            $code = 'area.'.$area->slug;
            $names[$code] = [
                'name' => $area->name,
                'permissions' => $this->allowFor($area->name),
            ];

            $names[$code.'.raids'] = [
                'name' => $area->name.' Raidar',
                'permissions' => $this->allowFor($area->slug.'-raids'),
            ];
            $names[$code.'.spawns'] = [
                'name' => $area->name.' Spawns',
                'permissions' => $this->allowFor($area->slug.'-pokemon'),
            ];
        }

        return $names;
    }

    protected function processChannels()
    {
        $channels = $this->getRequiredChannels();
        $channelsLocal = Channel::whereType(0)->get()->keyBy('code');
        $channelsRemote = $this->channels->where('type', '=', 0);

        foreach ($channels as $code => $channel) {
            $dbChan = $channelsLocal[$code] ?? null;

            if ($dbChan && $channelsRemote->has($dbChan->discord_id)) {
                $this->line("Channel '{$channel['name']}' already exists.");
                continue;
            }

            $this->createChannel($code, $channel);
        }
    }

    protected function getRequiredChannels()
    {
        $channels = [];

        foreach ($this->areas as $area) {
            $prefix = "area.{$area->slug}.";
            $parent = $this->channels
                ->where('type', '=', 4)
                ->where('name', '=', $area->name)
                ->first();

            $channels[$prefix.'lounge'] = [
                'name' => 'lounge',
                'parent' => $parent->id,
            ];

            foreach ($this->teams as $team) {
                $channels[$prefix.$team] = [
                    'name' => $team.'-chat',
                    'parent' => $parent->id,
                    'permissions' => $this->allowFor($area->slug.'-'.$team),
                ];
            }
        }

        return $channels;
    }

    protected function loadRemoteChannels()
    {
        $channels = collect($this->guild()->getGuildChannels([
            'guild.id' => $this->guildId
        ]))->sortBy(function ($channel, $key) {
            return $channel->type . str_pad(
                $channel->position, 5, '0', STR_PAD_LEFT
            );
        });

        $this->channels = $channels->keyBy('id');
    }

    protected function createChannel($code, $channel)
    {
        $parent = $channel['parent'] ?? 0;
        $type = $parent ? 'channel' : 'category';
        $this->info("Creating {$type} {$channel['name']} with code {$code}");

        $data = [
            'guild.id' => $this->guildId,
            'name' => $channel['name'],
            'type' => $parent ? 0 : 4,
        ];

        if ($parent) {
            $data['parent_id'] = (int) $parent;
        }

        if (isset($channel['permissions']) && count($channel['permissions'])) {
            $data['permission_overwrites'] = $channel['permissions'];
        }

        $channel = $this->guild()->createGuildChannel($data);
        $this->channels[$channel->id] = $channel;

        return Channel::updateOrCreate(['code' => $code], [
            'discord_id' => $channel->id,
            'position' => $channel->position,
            'type' => $channel->type,
            'parent_id' => $parent,
        ]);
    }

    /**
     * Make a set of Overwites that allow access to a role
     *
     * @param  string   $role
     * @return Overwrite[]
     */
    protected function allowFor($role)
    {
        return [
            $this->makeReadPermission(),
            $this->makeReadPermission('developers', true),
            $this->makeReadPermission($role, true),
        ];
    }

    /**
     * Generate a new Overwrite for a given role
     *
     * @param  string   $role   The role permissions will apply to
     * @param  boolean  $allow  Whether to allow or deny read access
     * @return Overwrite
     */
    protected function makeReadPermission($role = '@everyone', $allow = false)
    {
        $allow = $allow ? 'allow' : 'deny';

        return new Overwrite([
            'id' => $this->findRole($role)->id,
            'type' => 'role',
            $allow => 1024,
        ]);
    }

    /**
     * Search for a role by name
     *
     * @param  string  $name
     * @return object
     */
    protected function findRole($name)
    {
        return $this->roles->where('name', '=', $name)->first();
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

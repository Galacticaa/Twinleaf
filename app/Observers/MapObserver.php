<?php

namespace Twinleaf\Observers;

use Activity;
use Twinleaf\Map;

class MapObserver
{
    public function saved(Map $map)
    {
        $changes = $map->getChanges();
        unset($changes['updated_at'], $changes['started_at']);

        if (count($changes) || !$map->getOriginal('id')) {
            Activity::log([
                'contentId' => $map->id,
                'contentType' => 'map',
                'action' => 'create',
                'description' => sprintf(
                    'Map "<a href="%s">%s</a>" was created.',
                    route('maps.show', ['map' => $map]),
                    $map->name
                ),
                'updated' => (bool) $map->getOriginal('id'),
            ]);
        }
    }

    public function deleted(Map $map)
    {
        Activity::log([
            'contentId' => $map->id,
            'contentType' => 'map',
            'action' => 'delete',
            'description' => sprintf(
                'Map "<a href="%s">%s</a>" was deleted.',
                route('maps.show', ['map' => $map]),
                $map->name
            ),
        ]);
    }
}

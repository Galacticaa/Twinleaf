<?php

namespace Twinleaf\Observers;

use Activity;
use Twinleaf\MapArea;

class MapAreaObserver
{
    public function saved(MapArea $area)
    {
        $changes = $area->getChanges();
        unset($changes['updated_at'], $changes['started_at']);

        if (count($changes) || !$area->getOriginal('id')) {
            Activity::log([
                'contentId' => $area->id,
                'contentType' => 'map_area',
                'action' => 'create',
                'description' => sprintf(
                    'Area "<a href="%s">%s</a>" was created.',
                    route('maps.areas.show', ['map' => $area->map, 'area' => $area]),
                    $area->name
                ),
                'updated' => (bool) $area->getOriginal('id'),
            ]);
        }
    }

    public function deleted(MapArea $area)
    {
        Activity::log([
            'contentId' => $area->id,
            'contentType' => 'map_area',
            'action' => 'delete',
            'description' => sprintf('Area "<a href="#">%s</a>" was deleted.', $area->name),
        ]);
    }
}

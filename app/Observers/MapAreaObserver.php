<?php

namespace Twinleaf\Observers;

use Twinleaf\MapArea;

class MapAreaObserver
{
    public function saved(MapArea $area)
    {
        $changes = $area->getChanges();
        unset($changes['updated_at'], $changes['started_at']);

        if (count($changes) || !$area->getOriginal('id')) {
            $area->writeLog('create', sprintf(
                'Area "<a href="%s">%s</a>" was created.',
                route('maps.areas.show', ['map' => $area->map, 'area' => $area]),
                $area->name
            ), null, (bool) $area->getOriginal('id'));
        }
    }

    public function deleted(MapArea $area)
    {
        $message = "Area '<strong>{$area->name}</strong> was deleted.";

        $area->writeLog('delete', $message);
    }
}

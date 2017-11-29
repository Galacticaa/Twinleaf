<?php

namespace Twinleaf\Observers;

use Twinleaf\Map;

class MapObserver
{
    public function saved(Map $map)
    {
        $changes = $map->getChanges();
        unset($changes['updated_at'], $changes['started_at']);

        if (count($changes) || !$map->getOriginal('id')) {
            $map->writeLog('create', sprintf(
                'Map "<a href="%s">%s</a>" was created.',
                $map->url(), $map->name
            ), null, (bool) $map->getOriginal('id'));
        }
    }

    public function deleted(Map $map)
    {
        $message = "Map <strong>{$map->name}</strong> was deleted.";

        $map->writeLog('delete', $message);
    }
}

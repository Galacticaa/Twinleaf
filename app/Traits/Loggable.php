<?php

namespace Twinleaf\Traits;

Trait Loggable
{
    public function writeLog($action = 'x', $message = null, $details = null, $updated = null)
    {
        $log = [
            'contentId' => $this->id,
            'contentType' => $this->logType,
            'action' => $action,
            'description' => $message ?: $this->defaultLogMessage(),
        ];

        if ($details) {
            $log['details'] = $details;
        }

        if ($updated) {
            $log['updated'] = $updated;
        }

        Activity::log($log);
    }

    protected function defaultLogMessage($action)
    {
        $suffix = [
            'stop' => 'ped',
            'start' => 'ed',
            'restart' => 'ed',
        ];

        return sprintf(
            '<a href="%s">%s</a> was %s.',
            $this->url(),
            $this->name,
            $action.$suffix[$action]
        );
    }
}

<?php

namespace Latus\Installer\Listeners;

use Jackiedo\DotenvEditor\Facades\DotenvEditor;
use Latus\Installer\Events\DatabaseDetailsProvided;

class UpdateDatabaseDetailsInEnvFile
{
    public function handle(DatabaseDetailsProvided $event)
    {
        DotenvEditor::setKeys([
            'DB_CONNECTION' => $event->details['driver'],
            'DB_HOST' => $event->details['host'],
            'DB_PORT' => $event->details['port'],
            'DB_DATABASE' => $event->details['database'],
            'DB_USERNAME' => $event->details['username'],
            'DB_PASSWORD' => $event->details['password'],
            'DB_DRIVER' => $event->details['driver'],
            'DB_PREFIX' => $event->details['prefix'],
        ]);

        DotenvEditor::save();
    }
}
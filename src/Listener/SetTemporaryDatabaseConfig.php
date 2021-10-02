<?php

namespace Latus\Installer\Listeners;

use Illuminate\Support\Facades\Config;
use Latus\Installer\Events\DatabaseDetailsProvided;

class SetTemporaryDatabaseConfig
{
    public function handle(DatabaseDetailsProvided $event)
    {
        Config::set('database.connections.latus_installer', [
            'driver' => $event->details['driver'],
            'host' => $event->details['host'],
            'database' => $event->details['database'],
            'username' => $event->details['username'],
            'password' => $event->details['password'],
            'port' => $event->details['port'],
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => $event->details['prefix'],
            'strict' => false,
        ]);
    }
}
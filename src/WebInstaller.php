<?php

namespace Latus\Installer;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class WebInstaller
{

    protected function setTemporaryConnectionDetails(array $details)
    {
        Config::set('database.connections.latus_installer', [
            'driver' => $details['driver'],
            'host' => $details['host'],
            'database' => $details['database'],
            'username' => $details['username'],
            'password' => $details['password'],
            'port' => $details['port'],
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => $details['prefix'],
            'strict' => false,
        ]);
    }

    /**
     * Attempt a database connection
     *
     * @param array $details
     * @return bool
     */
    public function attemptConnectionWithDetails(array $details): bool
    {
        $this->setTemporaryConnectionDetails($details);

        if (!DB::connection('latus_installer') || !DB::connection('latus_installer')->getDatabaseName()) {
            return false;
        }

        return true;
    }
}
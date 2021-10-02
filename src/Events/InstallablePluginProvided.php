<?php

namespace Latus\Installer\Events;

use Illuminate\Foundation\Events\Dispatchable;

class InstallablePluginProvided
{
    use Dispatchable;

    public function __construct(
        public array $pluginData
    )
    {
    }
}
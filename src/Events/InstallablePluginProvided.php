<?php

namespace Latus\Installer\Events;

class InstallablePluginProvided
{
    public function __construct(
        public array $pluginData
    )
    {
    }
}
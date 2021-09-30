<?php

namespace Latus\Installer\Listeners;

use Latus\Installer\Events\InstallablePluginProvided;
use Latus\Plugins\Services\PluginService;

class InstallPlugin
{
    public function __construct(
        protected PluginService $pluginService,
    )
    {
    }

    public function handle(InstallablePluginProvided $event)
    {
        $this->pluginService->createPlugin($event->pluginData);
    }
}
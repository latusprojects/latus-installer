<?php

namespace Latus\Installer\Listeners;

use Latus\Installer\Events\InstallablePluginProvided;
use Latus\Installer\Listeners\Traits\InstallsPackage;
use Latus\Plugins\Exceptions\ComposerCLIException;
use Latus\Plugins\Models\Plugin;
use Latus\Plugins\Services\PluginService;

class InstallPlugin
{
    use InstallsPackage;

    public function __construct(
        protected PluginService $pluginService,
    )
    {
    }

    /**
     * @param InstallablePluginProvided $event
     * @throws ComposerCLIException
     */
    public function handle(InstallablePluginProvided $event)
    {
        /**
         * @var Plugin $plugin
         */
        $plugin = $this->pluginService->createPlugin($event->pluginData);
        $this->runComposer($plugin);
    }
}
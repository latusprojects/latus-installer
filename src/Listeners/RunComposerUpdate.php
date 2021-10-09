<?php

namespace Latus\Installer\Listeners;

use Latus\Installer\Events\PackagesInstalled;
use Latus\Plugins\Composer\CLInterface;

class RunComposerUpdate
{
    /**
     * @param PackagesInstalled $event
     */
    public function handle(PackagesInstalled $event)
    {
        $cli = new CLInterface();
        $cli->setIsQuiet(true);

        $cli->update();
    }
}
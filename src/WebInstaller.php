<?php

namespace Latus\Installer;

use Latus\Installer\Jobs\DisableWebInstaller;

class WebInstaller extends Installer
{
    public function destroy()
    {
        parent::destroy();

        DisableWebInstaller::dispatchAfterResponse();
    }
}
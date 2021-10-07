<?php

namespace Latus\Installer;

use Illuminate\Console\Command;
use Latus\Installer\Jobs\DisableWebInstaller;

class ConsoleInstaller extends Installer
{
    protected Command $cli;

    public function setCli(Command $cli)
    {
        $this->cli = $cli;
    }

}
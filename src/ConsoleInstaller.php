<?php

namespace Latus\Installer;

use Illuminate\Console\Command;

class ConsoleInstaller extends Installer
{
    protected Command $cli;

    public function setCli(Command $cli)
    {
        $this->cli = $cli;
    }
}
<?php

namespace Latus\Installer;

use Illuminate\Console\Command;

class ConsolePackageUpdater extends PackageUpdater
{
    protected Command $command;

    public function setCommand(Command $command)
    {
        $this->command = $command;
    }

    public function updateComposerPackage()
    {
        $this->command->info('Installing package...');
        parent::updateComposerPackage();
        $this->command->info('Package updated!');
    }
}
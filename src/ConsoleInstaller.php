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

    public function installComposerPackages()
    {
        $this->cli->info('Installing themes and plugins...');
        parent::installComposerPackages();
        $this->cli->info('Plugins and Themes installed!');
    }

    public function dispatchPackagesInstalledEvent()
    {
        $this->cli->info('Installation is almost finished, composer is now downloading all required themes and plugins.');
        $this->cli->warn('This might take some time, please do not terminate this session until the installation is finished.');
        parent::dispatchPackagesInstalledEvent();
    }

}
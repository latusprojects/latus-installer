<?php

namespace Latus\Installer\Listeners\Traits;

use Latus\Plugins\Composer\Package;
use Latus\Plugins\Composer\PackageFileHandler;
use Latus\Plugins\Models\Plugin;
use Latus\Plugins\Models\Theme;

trait InstallsPackage
{
    use RequiresMasterRepository;

    protected function requiresPackageToFile(Theme|Plugin $package)
    {
        $fileHandler = new PackageFileHandler();
        $composerPackage = new Package($this->getMasterRepository(), $package);
        $fileHandler->setPackage($composerPackage);
        $fileHandler->updateVersion();
    }
}
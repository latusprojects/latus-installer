<?php

namespace Latus\Installer;

use Latus\Plugins\Composer\CLInterface;
use Latus\Plugins\Models\Plugin;
use Latus\Plugins\Models\Theme;

abstract class PackageUpdater
{
    public function __construct(
        protected Plugin|Theme $package,
    )
    {
    }

    protected function getCLI(): CLInterface
    {
        return app(CLInterface::class);
    }

    public function updateComposerPackage()
    {
        $cli = $this->getCLI();
        $cli->setIsQuiet(true);
        
        $this->getCLI()->updatePackage($this->package->name);
    }

}
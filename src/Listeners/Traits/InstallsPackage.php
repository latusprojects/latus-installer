<?php

namespace Latus\Installer\Listeners\Traits;

use Latus\Plugins\Composer\Conductor;
use Latus\Plugins\Composer\Package;
use Latus\Plugins\Exceptions\ComposerCLIException;
use Latus\Plugins\Models\Plugin;
use Latus\Plugins\Models\Theme;

trait InstallsPackage
{
    use RequiresMasterRepository;

    /**
     * @param Theme|Plugin $package
     * @throws ComposerCLIException
     */
    protected function runComposer(Theme|Plugin $package)
    {
        $package = new Package($this->getMasterRepository(), $package);

        /**
         * @var Conductor $conductor
         */
        $conductor = app(Conductor::class);
        $conductor->installOrUpdatePackage($package);
    }
}
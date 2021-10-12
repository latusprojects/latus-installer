<?php

namespace Latus\Installer;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Latus\Helpers\Paths;
use Latus\Plugins\Composer\CLInterface;

class InstallationPurger
{
    protected function getComposerFileContents(): string
    {
        return File::get(Paths::basePath('composer.json'));
    }

    protected function setComposerFileContents(string $contents)
    {
        File::put(Paths::basePath('composer.json'), $contents);
    }

    /**
     * @throws \Exception
     */
    public function run()
    {
        $this->removeCachedListeners();
        $this->removeMetaPackages();
        $this->purgeDatabase();
        $this->removeInstallationLockFile();
    }

    protected function removeCachedListeners()
    {
        $filePath = Paths::basePath('bootstrap/cache/latus-package-events.php');
        if (stream_resolve_include_path($filePath)) {
            File::delete($filePath);
        }
    }

    protected function purgeDatabase()
    {
        Artisan::call(command: 'migrate:reset');
    }

    /**
     * @throws \Exception
     */
    protected function removeMetaPackages()
    {
        $cli = new CLInterface();
        $cli->setIsQuiet(true);

        $packages = array(
            'latus-packages/plugins',
            'latus-packages/themes',
        );

        foreach ($packages as $package) {
            $cli->removePackage($package, false);
            $cli->removeRepository($package);
        }

        File::deleteDirectory(Paths::pluginPath());
        File::deleteDirectory(Paths::themePath());
    }

    protected function removeInstallationLockFile()
    {
        $filePath = Paths::basePath('.installed');

        if (File::exists($filePath)) {
            File::delete($filePath);
        }
    }
}
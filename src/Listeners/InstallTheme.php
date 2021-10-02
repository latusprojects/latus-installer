<?php

namespace Latus\Installer\Listeners;

use Latus\Installer\Events\InstallableThemeProvided;
use Latus\Installer\Listeners\Traits\InstallsPackage;
use Latus\Plugins\Exceptions\ComposerCLIException;
use Latus\Plugins\Models\Theme;
use Latus\Plugins\Services\ThemeService;

class InstallTheme
{
    use InstallsPackage;

    public function __construct(
        protected ThemeService $themeService,
    )
    {
    }

    /**
     * @param InstallableThemeProvided $event
     * @throws ComposerCLIException
     */
    public function handle(InstallableThemeProvided $event)
    {
        /**
         * @var Theme $theme
         */
        $theme = $this->themeService->createTheme($event->themeData);
        $this->runComposer($theme);
    }
}
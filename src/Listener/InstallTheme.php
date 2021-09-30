<?php

namespace Latus\Installer\Listeners;

use Latus\Installer\Events\InstallableThemeProvided;
use Latus\Plugins\Services\ThemeService;

class InstallTheme
{
    public function __construct(
        protected ThemeService $themeService,
    )
    {
    }

    public function handle(InstallableThemeProvided $event)
    {
        $this->themeService->createTheme($event->themeData);
    }
}
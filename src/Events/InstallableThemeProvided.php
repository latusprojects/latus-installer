<?php

namespace Latus\Installer\Events;

class InstallableThemeProvided
{
    public function __construct(
        public array $themeData,
    )
    {
    }
}
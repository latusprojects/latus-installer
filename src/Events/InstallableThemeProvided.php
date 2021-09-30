<?php

namespace Latus\Installer\Events;

use Illuminate\Foundation\Events\Dispatchable;

class InstallableThemeProvided
{
    use Dispatchable;

    public function __construct(
        public array $themeData,
    )
    {
    }
}
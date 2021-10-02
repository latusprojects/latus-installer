<?php

namespace Latus\Installer\Events;

use Illuminate\Foundation\Events\Dispatchable;

class ActiveModulesProvided
{
    use Dispatchable;

    public function __construct(
        public array $activeModules,
    )
    {
    }
}
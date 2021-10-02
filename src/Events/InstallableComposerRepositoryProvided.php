<?php

namespace Latus\Installer\Events;

use Illuminate\Foundation\Events\Dispatchable;

class InstallableComposerRepositoryProvided
{
    use Dispatchable;

    public function __construct(
        public array $repositoryData,
    )
    {
    }
}
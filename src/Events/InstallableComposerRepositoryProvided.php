<?php

namespace Latus\Installer\Events;

class InstallableComposerRepositoryProvided
{
    public function __construct(
        public array $repositoryData,
    )
    {
    }
}
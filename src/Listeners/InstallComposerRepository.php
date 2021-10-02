<?php

namespace Latus\Installer\Listeners;

use Latus\Installer\Events\InstallableComposerRepositoryProvided;
use Latus\Plugins\Services\ComposerRepositoryService;

class InstallComposerRepository
{
    public function __construct(
        protected ComposerRepositoryService $composerRepositoryService,
    )
    {
    }

    public function handle(InstallableComposerRepositoryProvided $event)
    {
        $this->composerRepositoryService->createRepository($event->repositoryData);
    }
}
<?php

namespace Latus\Installer\Listeners\Traits;

use Latus\Plugins\Services\ComposerRepositoryService;

trait RequiresComposerRepositoryService
{
    protected ComposerRepositoryService $composerRepositoryService;

    protected function getComposerRepositoryService(): ComposerRepositoryService
    {
        if (!isset($this->{'composerRepositoryService'})) {
            $this->composerRepositoryService = app(ComposerRepositoryService::class);
        }

        return $this->composerRepositoryService;
    }
}
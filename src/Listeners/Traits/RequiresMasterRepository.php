<?php

namespace Latus\Installer\Listeners\Traits;

use Latus\Plugins\Models\ComposerRepository;

trait RequiresMasterRepository
{
    use RequiresComposerRepositoryService;

    protected ComposerRepository $masterRepository;

    protected function getMasterRepository(): ComposerRepository
    {
        if (!isset($this->{'masterRepository'})) {
            /**
             * @var ComposerRepository $masterRepository
             */
            $masterRepository = $this->getComposerRepositoryService()->findByName('latusprojects.repo.repman.io');
            $this->masterRepository = $masterRepository;
        }

        return $this->masterRepository;
    }
}
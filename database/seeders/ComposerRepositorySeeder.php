<?php

namespace Latus\Installer\Database\Seeders;

use Illuminate\Database\Seeder;
use Latus\Plugins\Models\ComposerRepository;
use Latus\Plugins\Services\ComposerRepositoryService;

class ComposerRepositorySeeder extends Seeder
{
    public function __construct(
        protected ComposerRepositoryService $repositoryService
    )
    {
    }

    public const COMPOSER_REPOSITORIES = [
        ['name' => 'local-plugins', 'type' => 'path', 'url' => 'plugins/local', 'status' => ComposerRepository::STATUS_ACTIVATED],
        ['name' => 'local-themes', 'type' => 'path', 'url' => 'themes/local', 'status' => ComposerRepository::STATUS_ACTIVATED],
        ['name' => 'local-dev', 'type' => 'path', 'url' => 'dev-packages/local', 'status' => ComposerRepository::STATUS_ACTIVATED],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (self::COMPOSER_REPOSITORIES as $repository) {
            if (!$this->repositoryService->findByName($repository['name'])) {
                $this->repositoryService->createRepository($repository);
            }
        }
    }
}

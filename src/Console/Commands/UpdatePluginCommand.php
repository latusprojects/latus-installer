<?php


namespace Latus\Installer\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Latus\Helpers\Paths;
use Latus\Installer\ConsoleInstaller;
use Latus\Installer\ConsolePackageUpdater;
use Latus\Installer\InstallationPurger;
use Latus\Plugins\Models\Plugin;
use Latus\Plugins\Services\PluginService;

class UpdatePluginCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'latus:update-plugin {package}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates a plugin';

    public function __construct(
        protected PluginService $pluginService,
    )
    {
        parent::__construct();
    }

    protected function getPlugin(): Plugin|null
    {
        return $this->pluginService->findByName($this->argument('package'));
    }

    /**
     * Execute the console command.
     *
     * @return int
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        if (!($plugin = $this->getPlugin())) {
            $this->error('Unknown package: "' . $this->argument('package') . '". Plugin is not installed.');
            return 1;
        }

        $updater = new ConsolePackageUpdater($plugin);
        $updater->setCommand($this);

        $updater->updateComposerPackage();

        $this->info('Plugin was successfully updated.');

        return 0;
    }

}
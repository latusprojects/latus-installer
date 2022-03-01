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
use Latus\Plugins\Models\Theme;
use Latus\Plugins\Services\ThemeService;

class UpdateThemeCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'latus:update-theme {package}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates a theme';

    public function __construct(
        protected ThemeService $themeService,
    )
    {
        parent::__construct();
    }

    protected function getTheme(): Theme|null
    {
        return $this->themeService->findByName($this->argument('package'));
    }

    /**
     * Execute the console command.
     *
     * @return int
     * @throws FileNotFoundException
     */
    public function handle(): int
    {
        if (!($theme = $this->getTheme())) {
            $this->error('Unknown package: "' . $this->argument('package') . '". Theme is not installed.');
            return 1;
        }

        $updater = new ConsolePackageUpdater($theme);
        $updater->setCommand($this);

        $updater->updateComposerPackage();

        $this->info('Theme was successfully updated.');

        return 0;
    }

}
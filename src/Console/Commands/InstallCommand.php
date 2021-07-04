<?php


namespace Latus\Installer\Console\Commands;


use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'latus:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Guided latus installer, also available as web-installer under <your-website>/install';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        return 0;
    }
}
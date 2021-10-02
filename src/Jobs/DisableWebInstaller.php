<?php

namespace Latus\Installer\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\File;

class DisableWebInstaller implements ShouldQueue
{
    use Dispatchable;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $maintenanceFile = storage_path('framework/maintenance.php');
        if (File::exists($maintenanceFile)) {
            File::delete($maintenanceFile);
        }
    }
}
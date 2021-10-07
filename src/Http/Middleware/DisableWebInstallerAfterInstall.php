<?php

namespace Latus\Installer\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Latus\Helpers\Paths;
use Latus\Installer\Jobs\DisableWebInstaller;

class DisableWebInstallerAfterInstall
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (defined('LATUS_INSTALLER') && File::exists(Paths::basePath('.installed'))) {
            DisableWebInstaller::dispatchSync();
        }

        return $next($request);
    }
}
<?php

use Illuminate\Support\Facades\Route;
use Latus\Installer\Http\Controllers\InstallController;

if (defined('LATUS_INSTALLER')) {
    Route::get('/', function () {
        return redirect('/install');
    });

    Route::get('/install/{?step}', [InstallController::class, 'showInstall']);

    /**
     * Redirect all other routes to the installer
     */
    Route::get('/{any}', function () {

        return redirect('/install');

    })->where('any', '.*');
}
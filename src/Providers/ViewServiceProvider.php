<?php

namespace Latus\Installer\Providers;

class ViewServiceProvider extends \Illuminate\View\ViewServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'latus-installer');
    }
}
<?php

namespace Latus\Installer\Listeners;

use Latus\Installer\Events\ActiveModulesProvided;
use Latus\Settings\Models\Setting;
use Latus\Settings\Services\SettingService;

class UpdateActiveModules
{
    public function handle(ActiveModulesProvided $event, SettingService $settingService)
    {
        /**
         * @var Setting $setting
         */
        $setting = $settingService->findByKey('active_modules');
        $settingService->setSettingValue($setting, json_encode($event->activeModules));
    }
}
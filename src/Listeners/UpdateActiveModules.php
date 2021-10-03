<?php

namespace Latus\Installer\Listeners;

use Latus\Installer\Events\ActiveModulesProvided;
use Latus\Settings\Models\Setting;
use Latus\Settings\Services\SettingService;

class UpdateActiveModules
{
    public function __construct(
        protected SettingService $settingService
    )
    {
    }

    public function handle(ActiveModulesProvided $event,)
    {
        /**
         * @var Setting $setting
         */
        $setting = $this->settingService->findByKey('active_modules');
        $this->settingService->setSettingValue($setting, json_encode($event->activeModules));
    }
}
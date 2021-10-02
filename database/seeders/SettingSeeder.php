<?php


namespace Latus\Installer\Database\Seeders;


use Illuminate\Database\Seeder;
use Latus\Settings\Services\SettingService;

class SettingSeeder extends Seeder
{
    public function __construct(
        protected SettingService $settingService
    )
    {
    }

    public const SETTINGS = [
        ['key' => 'active_themes', 'value' => []],
        ['key' => 'active_modules', 'value' => []],
        ['key' => 'disabled_modules', 'value' => []],
        ['key' => 'main_repository_name', 'value' => 'latusprojects.repo.repman.io']
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (self::SETTINGS as $setting) {
            if (!$this->settingService->findByKey($setting['key'])) {
                if (is_array($setting['value'])) {
                    $setting['value'] = json_encode($setting['value']);
                }
                $this->settingService->createSetting($setting);
            }
        }
    }

}
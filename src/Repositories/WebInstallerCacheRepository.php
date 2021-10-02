<?php

namespace Latus\Installer\Repositories;

use Illuminate\Support\Facades\Cache;

class WebInstallerCacheRepository
{
    public const CACHE_KEY = 'latus-installer';

    public function getCurrentStep(): string
    {
        return Cache::get(self::CACHE_KEY)['atStep'];
    }

    public function setCurrentStep(string $step)
    {
        $cache = Cache::get(self::CACHE_KEY);
        $cache['atStep'] = $step;
        Cache::put(self::CACHE_KEY, $cache);
    }

    public function getStepData(string $step): array
    {
        $cache = Cache::get(self::CACHE_KEY);
        return $cache['steps'][$step] ?? [];
    }

    public function destroy()
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function putStepDetails(string $step, array $details)
    {
        $cache = Cache::get(self::CACHE_KEY);
        $cache['steps'][$step] = $details;
        Cache::put(self::CACHE_KEY, $cache);
    }

    public function getCacheDetails(string $step): array
    {
        return Cache::get(self::CACHE_KEY)['steps'][$step];
    }

    public function ensureCacheHasKey()
    {
        Cache::get(self::CACHE_KEY) ?? Cache::put(self::CACHE_KEY, ['atStep' => 'start', 'steps' => [
            'database' => ['host' => 'localhost', 'driver' => 'mysql'],
            'app' => ['url' => 'https://'],
            'user' => []
        ]]);
    }
}
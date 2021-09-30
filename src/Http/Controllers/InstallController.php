<?php

namespace Latus\Installer\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Latus\Installer\Http\Requests\CheckDatabaseDetailsRequest;
use Latus\Installer\WebInstaller;

class InstallController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public const STEP_START = 'step';
    public const STEP_DATABASE = 'database';
    public const STEP_APP = 'app';
    public const STEP_USER = 'user';
    public const STEP_COMPLETE = 'complete';

    public static array $stepIndexes = [
        self::STEP_START => 0,
        self::STEP_DATABASE => 1,
        self::STEP_APP => 2,
        self::STEP_USER => 3,
        self::STEP_COMPLETE => 4,
    ];

    public function __construct(
        protected WebInstaller $installer
    )
    {
    }

    public function checkDatabaseDetails(CheckDatabaseDetailsRequest $request): JsonResponse
    {
        return $this->installer->attemptConnectionWithDetails($request->validated())
            ? response()->json([
                'message' => 'connection successful'
            ])
            : response('Not Found', 404)->json([
                'message' => 'connection using provided details failed'
            ]);
    }

    public function showInstall(string|null $step): View
    {
        $this->ensureCacheHasKey();

        if ($step === null || !$this->isValidStep($step)) {
            redirect('/install/' . $this->getCurrentStep());
        }

        return \view('latus-installer::steps.' . $step)->with(['data' => $this->getStepData($step)]);
    }

    protected function isValidStep(string $step): bool
    {
        return (!isset($step, self::$stepIndexes) || self::$stepIndexes[$step] < $this->getCurrentStepIndex());
    }

    protected function getCurrentStepIndex(): int
    {
        return self::$stepIndexes[Cache::get('latus-installer')['atStep']];
    }

    protected function getCurrentStep(): string
    {
        return Cache::get('latus-installer')['atStep'];
    }

    protected function getStepData(string $step): array
    {
        $cache = Cache::get('latus-installer');
        return $cache['steps'][$step] ?? [];
    }

    protected function ensureCacheHasKey()
    {
        Cache::get('latus-installer') ?? Cache::put('latus-installer', ['atStep' => 'start', 'steps' => [
            'database' => ['host' => 'localhost', 'driver' => 'mysql'],
            'app' => ['url' => 'https://']
        ]]);
    }
}
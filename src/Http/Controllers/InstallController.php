<?php

namespace Latus\Installer\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Latus\Installer\Http\Requests\SubmitAppDetailsRequest;
use Latus\Installer\Http\Requests\SubmitDatabaseDetailsRequest;
use Latus\Installer\Http\Requests\SubmitUserDetailsRequest;
use Latus\Installer\Repositories\WebInstallerCacheRepository;
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

    protected WebInstallerCacheRepository $cacheRepository;

    public function __construct(
        protected WebInstaller $installer
    )
    {
    }

    protected function getCacheRepository(): WebInstallerCacheRepository
    {
        if (!isset($this->{'cacheRepository'})) {
            $this->cacheRepository = app(WebInstallerCacheRepository::class);
        }

        return $this->cacheRepository;
    }

    public function showInstall(string|null $step): View
    {
        $this->getCacheRepository()->ensureCacheHasKey();

        if ($step === null || !$this->isValidStep($step)) {
            $this->redirectToCurrentStep();
        }

        return \view('latus-installer::steps.' . $step)->with(['data' => $this->getCacheRepository()->getStepData($step)]);
    }

    protected function isValidStep(string $step): bool
    {
        return (!isset($step, self::$stepIndexes) || self::$stepIndexes[$step] < $this->getCurrentStepIndex());
    }

    protected function getCurrentStepIndex(): int
    {
        return self::$stepIndexes[$this->getCacheRepository()->getCurrentStep()];
    }

    public function submitDatabaseDetails(SubmitDatabaseDetailsRequest $request): JsonResponse
    {
        $input = $request->validated();

        if (!$this->installer->attemptConnectionWithDetails($request->validated())) {
            return response('Not Found', 404)->json([
                'message' => 'database-connection could not be established using the provided details',
            ]);
        }

        $this->getCacheRepository()->putStepDetails('database', $input);

        return response()->json([
            'message' => 'database-details set'
        ]);
    }

    public function submitAppDetails(SubmitAppDetailsRequest $request): JsonResponse
    {
        $input = $request->validated();
        $this->getCacheRepository()->putStepDetails('app', $input);

        return response()->json([
            'message' => 'app-details set'
        ]);
    }

    public function submitUserDetails(SubmitUserDetailsRequest $request): JsonResponse
    {
        $input = $request->validated();
        $this->getCacheRepository()->putStepDetails('user', $input);

        return response()->json([
            'message' => 'user-details set'
        ]);
    }

    protected function redirectToCurrentStep()
    {
        redirect('/install/' . $this->getCacheRepository()->getCurrentStep());
    }

    public function finishInstall(): JsonResponse
    {
        if (!$this->isValidStep(self::STEP_COMPLETE)) {
            return response('Conflict', 409)->json([
                'message' => 'not all steps finished'
            ]);
        }

        $this->installer->build();

        try {
            $this->installer->apply(
                $this->getCacheRepository()->getStepData('database'),
                $this->getCacheRepository()->getStepData('app'),
                $this->getCacheRepository()->getStepData('user')
            );
        } catch (\Exception $exception) {
            return response('Internal Server Error', 500)->json([
                'message' => $exception->getMessage()
            ]);
        }

        $this->installer->destroy();

        return response()->json([
            'message' => 'install finished'
        ]);
    }
}
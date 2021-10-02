<?php

namespace Latus\Installer\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Latus\Installer\Http\Requests\CheckDatabaseDetailsRequest;
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
        $this->installer->build();
    }

    protected function getCacheRepository(): WebInstallerCacheRepository
    {
        if (!isset($this->{'cacheRepository'})) {
            $this->cacheRepository = app(WebInstallerCacheRepository::class);
        }

        return $this->cacheRepository;
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

    public function submitDatabaseDetails(SubmitDatabaseDetailsRequest $request)
    {
        $input = $request->validated();
        $this->getCacheRepository()->putStepDetails('database', $input);
    }

    public function submitAppDetails(SubmitAppDetailsRequest $request)
    {
        $input = $request->validated();
        $this->getCacheRepository()->putStepDetails('app', $input);
    }

    public function submitUserDetails(SubmitUserDetailsRequest $request)
    {
        $input = $request->validated();
        $this->getCacheRepository()->putStepDetails('user', $input);
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

        try {
            $this->installer->prepareDatabase();
        } catch (\Exception) {
            return response('Internal Server Error', 500)->json([
                'message' => 'could not run seeders or migrations'
            ]);
        }

        $this->provideDetails();

        $this->installer->installComposerPackages();

        $this->installer->destroy();

        return response()->json([
            'message' => 'install finished'
        ]);
    }


    protected function provideDetails()
    {
        $this->installer->provideDatabaseDetails($this->getCacheRepository()->getStepData('database'));
        $this->installer->provideAppDetails($this->getCacheRepository()->getStepData('app'));
        $this->installer->provideUserDetails($this->getCacheRepository()->getStepData('user'));
    }
}
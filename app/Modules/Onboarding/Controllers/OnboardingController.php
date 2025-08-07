<?php

namespace App\Modules\Onboarding\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Onboarding\Services\OnboardingService;

class OnboardingController extends Controller
{
    protected $authService;
    protected $onboardingService;

    public function __construct(AuthService $authService, OnboardingService $onboardingService)
    {
        $this->authService = $authService;
        $this->onboardingService = $onboardingService;
        $this->middleware('auth');
    }

    public function index()
    {
        $user = $this->authService->user();
        $progress = $this->onboardingService->getUserProgress($user);
        $steps = $this->onboardingService->getOnboardingSteps();

        return view('funcionario.index', compact('user', 'progress', 'steps'));
    }

    public function completeStep(string $step)
    {
        $user = $this->authService->user();
        $this->onboardingService->completeStep($user, $step);

        return redirect()->route('funcionario')->with('success', 'Etapa concluída com sucesso!');
    }
}
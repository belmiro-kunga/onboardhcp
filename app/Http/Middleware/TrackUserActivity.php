<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\UserActivityService;
use App\Models\UserActivity;

class TrackUserActivity
{
    protected UserActivityService $activityService;

    public function __construct(UserActivityService $activityService)
    {
        $this->activityService = $activityService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only track for authenticated users
        if (Auth::check() && $this->shouldTrackRequest($request)) {
            $this->trackPageView($request);
        }

        return $response;
    }

    /**
     * Determine if the request should be tracked
     */
    protected function shouldTrackRequest(Request $request): bool
    {
        // Don't track AJAX requests for activity tracking itself
        if ($request->ajax() && $this->isActivityTrackingRequest($request)) {
            return false;
        }

        // Don't track API requests
        if ($request->is('api/*')) {
            return false;
        }

        // Don't track asset requests
        if ($request->is('css/*', 'js/*', 'images/*', 'fonts/*', 'favicon.ico')) {
            return false;
        }

        // Only track GET requests for page views
        if ($request->method() !== 'GET') {
            return false;
        }

        return true;
    }

    /**
     * Check if this is an activity tracking related request
     */
    protected function isActivityTrackingRequest(Request $request): bool
    {
        return $request->is('admin/users/activity/*') || 
               $request->is('admin/activity/*') ||
               $request->routeIs('admin.users.activity.*') ||
               $request->routeIs('admin.activity.*');
    }

    /**
     * Track page view activity
     */
    protected function trackPageView(Request $request): void
    {
        $pageName = $this->getPageName($request);
        
        // Don't track if we can't determine a meaningful page name
        if (!$pageName) {
            return;
        }

        $this->activityService->logPageView($pageName, [
            'route_name' => $request->route()?->getName(),
            'parameters' => $request->route()?->parameters() ?? []
        ]);
    }

    /**
     * Get a human-readable page name from the request
     */
    protected function getPageName(Request $request): ?string
    {
        $routeName = $request->route()?->getName();
        $path = $request->path();

        // Map route names to readable page names
        $pageNames = [
            'admin.dashboard' => 'Dashboard Admin',
            'admin.users' => 'Gestão de Usuários',
            'admin.simulados.index' => 'Gestão de Simulados',
            'admin.simulados.create' => 'Criar Simulado',
            'admin.simulados.edit' => 'Editar Simulado',
            'admin.videos.index' => 'Gestão de Vídeos',
            'admin.videos.create' => 'Criar Vídeo',
            'admin.videos.edit' => 'Editar Vídeo',
            'funcionario' => 'Dashboard Funcionário',
            'onboarding.index' => 'Onboarding - Início',
            'onboarding.boas-vindas' => 'Onboarding - Boas Vindas',
            'onboarding.sobre-empresa' => 'Onboarding - Sobre a Empresa',
            'onboarding.historia' => 'Onboarding - História',
            'onboarding.departamentos' => 'Onboarding - Departamentos',
            'onboarding.cultura-valores' => 'Onboarding - Cultura e Valores',
            'onboarding.organograma' => 'Onboarding - Organograma',
            'simulados.index' => 'Simulados',
            'simulados.show' => 'Visualizar Simulado',
            'simulados.executar' => 'Executar Simulado',
            'simulados.resultado' => 'Resultado do Simulado',
            'profile.edit' => 'Editar Perfil'
        ];

        if ($routeName && isset($pageNames[$routeName])) {
            return $pageNames[$routeName];
        }

        // Try to generate a name from the path
        if (str_starts_with($path, 'admin/')) {
            $segments = explode('/', $path);
            if (count($segments) >= 2) {
                return 'Admin - ' . ucfirst($segments[1]);
            }
        }

        if (str_starts_with($path, 'onboarding/')) {
            return 'Onboarding';
        }

        if (str_starts_with($path, 'simulados/')) {
            return 'Simulados';
        }

        // Default fallback
        return ucfirst(str_replace(['/', '-', '_'], [' - ', ' ', ' '], $path));
    }
}

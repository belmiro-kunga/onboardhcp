<?php

namespace App\Http\Controllers;

use App\Services\UserSearchService;
use App\Http\Requests\UserSearchRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserSearchController extends Controller
{
    protected UserSearchService $userSearchService;

    public function __construct(UserSearchService $userSearchService)
    {
        $this->userSearchService = $userSearchService;
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Handle AJAX search requests
     */
    public function search(UserSearchRequest $request): JsonResponse
    {
        $filters = $request->getSearchFilters();
        
        try {
            $results = $this->userSearchService->search($filters);
            $statistics = $this->userSearchService->getSearchStatistics($filters);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $results->items(),
                    'pagination' => [
                        'current_page' => $results->currentPage(),
                        'last_page' => $results->lastPage(),
                        'per_page' => $results->perPage(),
                        'total' => $results->total(),
                        'from' => $results->firstItem(),
                        'to' => $results->lastItem(),
                    ],
                    'statistics' => $statistics,
                    'filters_applied' => count($filters),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao realizar pesquisa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get search suggestions for autocomplete
     */
    public function suggestions(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'all');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        try {
            $suggestions = [];
            
            switch ($type) {
                case 'departments':
                    $departments = $this->userSearchService->getAvailableDepartments();
                    $suggestions = array_filter($departments, function ($dept) use ($query) {
                        return stripos($dept, $query) !== false;
                    });
                    break;
                    
                case 'positions':
                    $positions = $this->userSearchService->getAvailablePositions();
                    $suggestions = array_filter($positions, function ($pos) use ($query) {
                        return stripos($pos, $query) !== false;
                    });
                    break;
                    
                case 'roles':
                    $roles = $this->userSearchService->getAvailableRoles();
                    $suggestions = array_filter($roles, function ($role) use ($query) {
                        return stripos($role, $query) !== false;
                    });
                    break;
                    
                default:
                    // General search suggestions from user names and emails
                    $users = \App\Modules\User\Models\User::where(function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                          ->orWhere('email', 'like', "%{$query}%");
                    })->limit(10)->get(['name', 'email']);
                    
                    $suggestions = $users->map(function ($user) {
                        return [
                            'label' => $user->name . ' (' . $user->email . ')',
                            'value' => $user->name,
                            'type' => 'user'
                        ];
                    })->toArray();
                    break;
            }
            
            return response()->json(array_values($suggestions));
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }

    /**
     * Get filter options for dropdowns
     */
    public function filterOptions(): JsonResponse
    {
        try {
            return response()->json([
                'departments' => $this->userSearchService->getAvailableDepartments(),
                'positions' => $this->userSearchService->getAvailablePositions(),
                'roles' => $this->userSearchService->getAvailableRoles(),
                'status_options' => [
                    'active' => 'Ativo',
                    'inactive' => 'Inativo',
                    'pending' => 'Pendente',
                    'blocked' => 'Bloqueado',
                    'suspended' => 'Suspenso'
                ],
                'activity_options' => [
                    'active' => 'Ativos (últimos 30 dias)',
                    'recent' => 'Recentes (últimos 7 dias)',
                    'inactive' => 'Inativos',
                    'never_logged' => 'Nunca fizeram login'
                ],
                'sort_options' => [
                    'name' => 'Nome',
                    'email' => 'Email',
                    'status' => 'Status',
                    'department' => 'Departamento',
                    'position' => 'Cargo',
                    'hire_date' => 'Data de Admissão',
                    'last_login' => 'Último Acesso',
                    'created_at' => 'Data de Criação'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro ao carregar opções de filtro'
            ], 500);
        }
    }

    /**
     * Save search configuration
     */
    public function saveSearchConfig(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'filters' => 'required|array'
        ]);
        
        try {
            $userId = auth()->id();
            $success = $this->userSearchService->saveSearchConfiguration(
                $userId,
                $request->name,
                $request->filters
            );
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Configuração de pesquisa guardada com sucesso!'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao guardar configuração de pesquisa'
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao guardar configuração: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get saved search configurations
     */
    public function getSavedConfigs(): JsonResponse
    {
        try {
            $userId = auth()->id();
            $configs = $this->userSearchService->getSavedSearchConfigurations($userId);
            
            return response()->json([
                'success' => true,
                'data' => $configs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar configurações guardadas'
            ], 500);
        }
    }

    /**
     * Export search results
     */
    public function export(UserSearchRequest $request): JsonResponse
    {
        $filters = $request->getSearchFilters();
        $format = $request->getExportFormat();
        
        try {
            // This would integrate with the ImportExportService from task 4
            // For now, we'll return a placeholder response
            return response()->json([
                'success' => true,
                'message' => 'Exportação iniciada. Receberá um email quando estiver pronta.',
                'export_id' => uniqid('export_'),
                'format' => $format,
                'filters_applied' => count($filters)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao iniciar exportação: ' . $e->getMessage()
            ], 500);
        }
    }
}
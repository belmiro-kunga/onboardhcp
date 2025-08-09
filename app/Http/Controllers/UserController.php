<?php

namespace App\Http\Controllers;

use App\Modules\User\Models\User;
use App\Http\Requests\UserRequest;
use App\Services\UserService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    protected UserService $userService;
    protected NotificationService $notificationService;

    public function __construct(UserService $userService, NotificationService $notificationService)
    {
        $this->userService = $userService;
        $this->notificationService = $notificationService;
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Display a listing of users
     */
    public function index()
    {
        $users = User::with(['roles'])->paginate(15);
        
        return view('admin.users', compact('users'));
    }

    /**
     * Store a newly created user
     */
    public function store(UserRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $userData = $request->validated();
            $userData['password'] = Hash::make($userData['password']);
            $userData['status'] = $userData['status'] ?? 'active';

            $user = User::create($userData);

            // Assign roles if provided
            if ($request->has('roles')) {
                $user->roles()->sync($request->roles);
            }

            // Send welcome email if requested
            if ($request->boolean('send_welcome_email', false)) {
                $this->notificationService->sendWelcomeEmail($user, $request->password);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuário criado com sucesso!',
                'user' => $user->load('roles')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating user: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar usuário: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified user
     */
    public function update(UserRequest $request, User $user): JsonResponse
    {
        try {
            DB::beginTransaction();

            $userData = $request->validated();
            
            // Only update password if provided
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($userData['password']);
            } else {
                unset($userData['password']);
            }

            $user->update($userData);

            // Update roles if provided
            if ($request->has('roles')) {
                $user->roles()->sync($request->roles);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso!',
                'user' => $user->fresh()->load('roles')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating user: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar usuário: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            // Prevent deletion of the current user
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível excluir o próprio usuário.'
                ], 400);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuário excluído com sucesso!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir usuário: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user status
     */
    public function updateStatus(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'status' => ['required', Rule::in(['active', 'inactive', 'pending', 'blocked', 'suspended'])],
            'status_reason' => 'nullable|string|max:500',
            'notify_user' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $oldStatus = $user->status;
            $newStatus = $request->status;

            // Update user status
            $user->update([
                'status' => $newStatus
            ]);

            // Log status change
            $this->logStatusChange($user, $oldStatus, $newStatus, $request->status_reason);

            // Send notification if requested
            if ($request->boolean('notify_user', false)) {
                $this->notificationService->sendStatusChangeNotification(
                    $user, 
                    $oldStatus, 
                    $newStatus, 
                    $request->status_reason
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Status do usuário alterado para '{$this->getStatusLabel($newStatus)}' com sucesso!",
                'user' => $user->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating user status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alterar status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update status for multiple users
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $request->validate([
            'user_ids' => 'required|string',
            'status' => ['required', Rule::in(['active', 'inactive', 'pending', 'blocked', 'suspended'])],
            'status_reason' => 'nullable|string|max:500',
            'notify_users' => 'boolean'
        ]);

        try {
            $userIds = explode(',', $request->user_ids);
            $userIds = array_filter(array_map('intval', $userIds));

            if (empty($userIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum usuário selecionado.'
                ], 400);
            }

            // Prevent changing status of current user
            if (in_array(auth()->id(), $userIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível alterar o status do próprio usuário.'
                ], 400);
            }

            DB::beginTransaction();

            $users = User::whereIn('id', $userIds)->get();
            $newStatus = $request->status;
            $updatedCount = 0;

            foreach ($users as $user) {
                $oldStatus = $user->status;
                
                if ($oldStatus !== $newStatus) {
                    $user->update(['status' => $newStatus]);
                    
                    // Log status change
                    $this->logStatusChange($user, $oldStatus, $newStatus, $request->status_reason);
                    
                    // Send notification if requested
                    if ($request->boolean('notify_users', false)) {
                        $this->notificationService->sendStatusChangeNotification(
                            $user, 
                            $oldStatus, 
                            $newStatus, 
                            $request->status_reason
                        );
                    }
                    
                    $updatedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Status de {$updatedCount} usuário(s) alterado para '{$this->getStatusLabel($newStatus)}' com sucesso!",
                'updated_count' => $updatedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error bulk updating user status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alterar status em massa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user status statistics
     */
    public function getStatusStatistics(): JsonResponse
    {
        try {
            $statistics = User::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $statusLabels = [
                'active' => 'Ativos',
                'inactive' => 'Inativos',
                'pending' => 'Pendentes',
                'blocked' => 'Bloqueados',
                'suspended' => 'Suspensos'
            ];

            $formattedStats = [];
            foreach ($statusLabels as $status => $label) {
                $formattedStats[] = [
                    'status' => $status,
                    'label' => $label,
                    'count' => $statistics[$status] ?? 0
                ];
            }

            return response()->json([
                'success' => true,
                'statistics' => $formattedStats,
                'total' => array_sum($statistics)
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting status statistics: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar estatísticas'
            ], 500);
        }
    }

    /**
     * Log status change
     */
    private function logStatusChange(User $user, string $oldStatus, string $newStatus, ?string $reason): void
    {
        Log::info('User status changed', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'reason' => $reason,
            'changed_by' => auth()->user()->email,
            'timestamp' => now()
        ]);
    }

    /**
     * Force reset user password
     */
    public function forceResetPassword(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
            'notify_user' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            // Prevent resetting password of current user through this method
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Use a opção de perfil para alterar sua própria senha.'
                ], 400);
            }

            // Update user password
            $user->update([
                'password' => Hash::make($request->new_password),
                'password_changed_at' => now()
            ]);

            // Log password reset
            Log::info('Password force reset', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'reset_by' => auth()->user()->email,
                'timestamp' => now()
            ]);

            // Send notification if requested
            if ($request->boolean('notify_user', false)) {
                $this->notificationService->sendPasswordResetNotification(
                    $user,
                    $request->new_password
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Senha do usuário redefinida com sucesso!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error force resetting password: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao redefinir senha: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get status label
     */
    private function getStatusLabel(string $status): string
    {
        $labels = [
            'active' => 'Ativo',
            'inactive' => 'Inativo',
            'pending' => 'Pendente',
            'blocked' => 'Bloqueado',
            'suspended' => 'Suspenso'
        ];

        return $labels[$status] ?? $status;
    }
}

<?php

namespace App\Services;

use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class UserService
{
    /**
     * Create a new user
     */
    public function createUser(array $userData): User
    {
        if (isset($userData['password'])) {
            $userData['password'] = Hash::make($userData['password']);
        }

        if (isset($userData['avatar']) && $userData['avatar'] instanceof UploadedFile) {
            $userData['avatar'] = $this->handleAvatarUpload($userData['avatar']);
        }

        return User::create($userData);
    }

    /**
     * Update an existing user
     */
    public function updateUser(User $user, array $userData): User
    {
        if (isset($userData['password']) && !empty($userData['password'])) {
            $userData['password'] = Hash::make($userData['password']);
        } else {
            unset($userData['password']);
        }

        if (isset($userData['avatar']) && $userData['avatar'] instanceof UploadedFile) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $userData['avatar'] = $this->handleAvatarUpload($userData['avatar']);
        }

        $user->update($userData);
        return $user->fresh();
    }

    /**
     * Handle avatar file upload
     */
    private function handleAvatarUpload(UploadedFile $file): string
    {
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('avatars', $filename, 'public');
        
        return $path;
    }

    /**
     * Get user status statistics
     */
    public function getStatusStatistics(): array
    {
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
        $total = 0;

        foreach ($statusLabels as $status => $label) {
            $count = $statistics[$status] ?? 0;
            $formattedStats[] = [
                'status' => $status,
                'label' => $label,
                'count' => $count,
                'percentage' => 0 // Will be calculated after total is known
            ];
            $total += $count;
        }

        // Calculate percentages
        foreach ($formattedStats as &$stat) {
            $stat['percentage'] = $total > 0 ? round(($stat['count'] / $total) * 100, 1) : 0;
        }

        return [
            'statistics' => $formattedStats,
            'total' => $total
        ];
    }

    /**
     * Get users by status
     */
    public function getUsersByStatus(string $status): \Illuminate\Database\Eloquent\Collection
    {
        return User::where('status', $status)
            ->with(['roles'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Bulk update user status
     */
    public function bulkUpdateStatus(array $userIds, string $status, ?string $reason = null): int
    {
        $updatedCount = 0;
        
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user && $user->status !== $status) {
                $user->update(['status' => $status]);
                $updatedCount++;
            }
        }

        return $updatedCount;
    }

    /**
     * Check if user can be deleted
     */
    public function canDeleteUser(User $user): array
    {
        $canDelete = true;
        $reasons = [];

        // Check if it's the current user
        if ($user->id === auth()->id()) {
            $canDelete = false;
            $reasons[] = 'Não é possível excluir o próprio usuário.';
        }

        // Check if user is the only admin
        if ($user->is_admin) {
            $adminCount = User::where('is_admin', true)->count();
            if ($adminCount <= 1) {
                $canDelete = false;
                $reasons[] = 'Não é possível excluir o último administrador do sistema.';
            }
        }

        return [
            'can_delete' => $canDelete,
            'reasons' => $reasons
        ];
    }

    /**
     * Get status label
     */
    public function getStatusLabel(string $status): string
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

    /**
     * Get status color class
     */
    public function getStatusColorClass(string $status): string
    {
        $colors = [
            'active' => 'bg-green-100 text-green-800',
            'inactive' => 'bg-gray-100 text-gray-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'blocked' => 'bg-red-100 text-red-800',
            'suspended' => 'bg-orange-100 text-orange-800'
        ];

        return $colors[$status] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Get status icon
     */
    public function getStatusIcon(string $status): string
    {
        $icons = [
            'active' => '✅',
            'inactive' => '❌',
            'pending' => '⏳',
            'blocked' => '🚫',
            'suspended' => '⏸️'
        ];

        return $icons[$status] ?? '❓';
    }
}

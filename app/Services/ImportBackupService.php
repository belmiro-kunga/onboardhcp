<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportBackupService
{
    /**
     * Create backup before import
     */
    public function createBackup(string $importId = null): string
    {
        $importId = $importId ?? uniqid('import_', true);
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupPath = "backups/users_backup_{$timestamp}_{$importId}.json";

        // Get all current users with their relationships
        $users = User::with(['roles', 'groups', 'profile'])->get();

        $backupData = [
            'metadata' => [
                'created_at' => now()->toISOString(),
                'import_id' => $importId,
                'total_users' => $users->count(),
                'laravel_version' => app()->version(),
                'backup_version' => '1.0'
            ],
            'users' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'department' => $user->department,
                    'position' => $user->position,
                    'hire_date' => $user->hire_date?->toISOString(),
                    'status' => $user->status,
                    'is_admin' => $user->is_admin,
                    'email_verified_at' => $user->email_verified_at?->toISOString(),
                    'created_at' => $user->created_at->toISOString(),
                    'updated_at' => $user->updated_at->toISOString(),
                    'roles' => $user->roles->pluck('name')->toArray(),
                    'groups' => $user->groups->pluck('name')->toArray(),
                    'profile' => $user->profile ? [
                        'bio' => $user->profile->bio,
                        'skills' => $user->profile->skills,
                        'preferences' => $user->profile->preferences,
                        'emergency_contact' => $user->profile->emergency_contact,
                    ] : null
                ];
            })->toArray()
        ];

        // Store backup
        Storage::put($backupPath, json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $backupPath;
    }

    /**
     * Restore from backup
     */
    public function restoreFromBackup(string $backupPath): bool
    {
        if (!Storage::exists($backupPath)) {
            throw new \Exception("Backup file not found: {$backupPath}");
        }

        $backupContent = Storage::get($backupPath);
        $backupData = json_decode($backupContent, true);

        if (!$backupData || !isset($backupData['users'])) {
            throw new \Exception("Invalid backup file format");
        }

        DB::beginTransaction();

        try {
            // Clear current users (be very careful with this!)
            // In a real implementation, you might want to be more selective
            User::query()->delete();

            // Restore users
            foreach ($backupData['users'] as $userData) {
                $user = User::create([
                    'id' => $userData['id'],
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'phone' => $userData['phone'],
                    'department' => $userData['department'],
                    'position' => $userData['position'],
                    'hire_date' => $userData['hire_date'] ? Carbon::parse($userData['hire_date']) : null,
                    'status' => $userData['status'],
                    'is_admin' => $userData['is_admin'],
                    'email_verified_at' => $userData['email_verified_at'] ? Carbon::parse($userData['email_verified_at']) : null,
                    'created_at' => Carbon::parse($userData['created_at']),
                    'updated_at' => Carbon::parse($userData['updated_at']),
                    'password' => bcrypt('temp_password_' . uniqid()) // Temporary password
                ]);

                // Restore roles
                if (!empty($userData['roles'])) {
                    $roleIds = \App\Models\Role::whereIn('name', $userData['roles'])->pluck('id');
                    $user->roles()->sync($roleIds);
                }

                // Restore groups
                if (!empty($userData['groups'])) {
                    $groupIds = \App\Models\UserGroup::whereIn('name', $userData['groups'])->pluck('id');
                    $user->groups()->sync($groupIds);
                }

                // Restore profile
                if ($userData['profile']) {
                    $user->profile()->create($userData['profile']);
                }
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * List available backups
     */
    public function listBackups(): array
    {
        $files = Storage::files('backups');
        $backups = [];

        foreach ($files as $file) {
            if (str_ends_with($file, '.json') && str_contains($file, 'users_backup_')) {
                $backups[] = [
                    'path' => $file,
                    'name' => basename($file),
                    'size' => Storage::size($file),
                    'created_at' => Storage::lastModified($file),
                    'created_at_human' => Carbon::createFromTimestamp(Storage::lastModified($file))->diffForHumans()
                ];
            }
        }

        // Sort by creation date (newest first)
        usort($backups, fn($a, $b) => $b['created_at'] <=> $a['created_at']);

        return $backups;
    }

    /**
     * Clean old backups
     */
    public function cleanOldBackups(): int
    {
        $retentionDays = config('user-search.import.backup.backup_retention_days', 30);
        $cutoffDate = now()->subDays($retentionDays);
        
        $files = Storage::files('backups');
        $deletedCount = 0;

        foreach ($files as $file) {
            if (str_ends_with($file, '.json') && str_contains($file, 'users_backup_')) {
                $fileDate = Carbon::createFromTimestamp(Storage::lastModified($file));
                
                if ($fileDate->lt($cutoffDate)) {
                    Storage::delete($file);
                    $deletedCount++;
                }
            }
        }

        return $deletedCount;
    }

    /**
     * Get backup metadata
     */
    public function getBackupMetadata(string $backupPath): array
    {
        if (!Storage::exists($backupPath)) {
            throw new \Exception("Backup file not found: {$backupPath}");
        }

        $backupContent = Storage::get($backupPath);
        $backupData = json_decode($backupContent, true);

        return $backupData['metadata'] ?? [];
    }
}
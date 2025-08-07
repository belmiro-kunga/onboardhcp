<?php

namespace App\Modules\User\Services;

use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;

class UserService
{
    public function getAllUsers(): Collection
    {
        return User::orderBy('name')->get();
    }

    public function getUserById(int $id): ?User
    {
        return User::find($id);
    }

    public function createUser(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $data['email_verified_at'] = now();
        
        return User::create($data);
    }

    public function updateUser(User $user, array $data): bool
    {
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $user->update($data);
    }

    public function deleteUser(User $user): bool
    {
        return $user->delete();
    }

    public function getTotalUsersCount(): int
    {
        return User::count();
    }

    public function getAdminsCount(): int
    {
        return User::admins()->count();
    }

    public function getEmployeesCount(): int
    {
        return User::employees()->count();
    }

    public function getActiveUsersCount(): int
    {
        // Consider users active if they logged in within the last 30 days
        return User::where('last_login_at', '>=', now()->subDays(30))->count();
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
}
<?php

namespace App\Services;

use App\Models\Role;
use App\Modules\User\Models\User;
use Illuminate\Support\Collection;

class RoleValidationService
{
    /**
     * Validate role assignment with advanced business rules
     */
    public function validateRoleAssignment(User $user, string $roleName): array
    {
        $errors = [];
        
        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            $errors[] = "Role '{$roleName}' não existe";
            return $errors;
        }

        // Check if user already has this role
        if ($user->hasRole($roleName)) {
            $errors[] = "Utilizador já possui o role '{$roleName}'";
        }

        // Business rule: Check mutually exclusive roles
        $mutuallyExclusiveRoles = $this->getMutuallyExclusiveRoles($roleName);
        foreach ($mutuallyExclusiveRoles as $exclusiveRole) {
            if ($user->hasRole($exclusiveRole)) {
                $errors[] = "Não é possível atribuir '{$roleName}' porque o utilizador já possui '{$exclusiveRole}' (roles mutuamente exclusivos)";
            }
        }

        // Business rule: Check department restrictions
        $departmentRestrictions = $this->getDepartmentRestrictions($roleName);
        if (!empty($departmentRestrictions) && !in_array($user->department, $departmentRestrictions)) {
            $errors[] = "Role '{$roleName}' só pode ser atribuído a utilizadores dos departamentos: " . implode(', ', $departmentRestrictions);
        }

        // Business rule: Check maximum users per role
        $maxUsers = $this->getMaxUsersPerRole($roleName);
        if ($maxUsers > 0) {
            $currentUsers = $role->users()->count();
            if ($currentUsers >= $maxUsers) {
                $errors[] = "Role '{$roleName}' já atingiu o limite máximo de {$maxUsers} utilizadores";
            }
        }

        // Business rule: Check user status
        if ($user->status !== 'active') {
            $errors[] = "Não é possível atribuir roles a utilizadores com status '{$user->status_label}'";
        }

        // Business rule: Check if user has required prerequisite roles
        $prerequisiteRoles = $this->getPrerequisiteRoles($roleName);
        foreach ($prerequisiteRoles as $prerequisiteRole) {
            if (!$user->hasRole($prerequisiteRole)) {
                $errors[] = "Para atribuir '{$roleName}', o utilizador deve primeiro ter o role '{$prerequisiteRole}'";
            }
        }
        
        return $errors;
    }

    /**
     * Validate role removal with advanced business rules
     */
    public function validateRoleRemoval(User $user, string $roleName): array
    {
        $errors = [];
        
        if (!$user->hasRole($roleName)) {
            $errors[] = "Utilizador não possui o role '{$roleName}'";
            return $errors;
        }

        // Business rule: Prevent removal of last admin role
        if (in_array($roleName, ['Super Admin', 'Admin'])) {
            $adminRoles = $user->roles()->whereIn('name', ['Super Admin', 'Admin'])->count();
            if ($adminRoles <= 1) {
                $totalAdmins = User::whereHas('roles', function($q) {
                    $q->whereIn('name', ['Super Admin', 'Admin']);
                })->count();
                
                if ($totalAdmins <= 1) {
                    $errors[] = "Não é possível remover o último role de administrador do sistema";
                }
            }
        }

        // Business rule: Check if role is required for user's department
        $requiredRoles = $this->getRequiredRolesForDepartment($user->department);
        if (in_array($roleName, $requiredRoles)) {
            $errors[] = "Role '{$roleName}' é obrigatório para utilizadores do departamento '{$user->department}'";
        }

        // Business rule: Check if other roles depend on this role
        $dependentRoles = $this->getDependentRoles($roleName);
        foreach ($dependentRoles as $dependentRole) {
            if ($user->hasRole($dependentRole)) {
                $errors[] = "Não é possível remover '{$roleName}' porque o utilizador possui '{$dependentRole}' que depende deste role";
            }
        }
        
        return $errors;
    }

    /**
     * Get mutually exclusive roles
     */
    protected function getMutuallyExclusiveRoles(string $roleName): array
    {
        $exclusiveRules = [
            'Student' => ['Employee', 'Manager', 'Admin', 'Super Admin'],
            'Employee' => ['Student'],
            'Manager' => ['Student'],
            'Admin' => ['Student'],
            'Super Admin' => ['Student']
        ];

        return $exclusiveRules[$roleName] ?? [];
    }

    /**
     * Get department restrictions for roles
     */
    protected function getDepartmentRestrictions(string $roleName): array
    {
        $departmentRules = [
            'Super Admin' => [], // No restrictions
            'Admin' => [], // No restrictions
            'Manager' => [], // No restrictions
            // Add specific department restrictions here
            // 'HR Manager' => ['recursos humanos'],
            // 'IT Admin' => ['ti', 'tecnologia'],
        ];

        return $departmentRules[$roleName] ?? [];
    }

    /**
     * Get maximum users per role
     */
    protected function getMaxUsersPerRole(string $roleName): int
    {
        $maxUserRules = [
            'Super Admin' => 3, // Maximum 3 super admins
            // Add other role limits here
        ];

        return $maxUserRules[$roleName] ?? 0; // 0 means no limit
    }

    /**
     * Get prerequisite roles
     */
    protected function getPrerequisiteRoles(string $roleName): array
    {
        $prerequisiteRules = [
            'Admin' => [], // No prerequisites
            'Manager' => ['Employee'], // Must be Employee first
            // Add other prerequisite rules here
        ];

        return $prerequisiteRules[$roleName] ?? [];
    }

    /**
     * Get required roles for department
     */
    protected function getRequiredRolesForDepartment(?string $department): array
    {
        if (!$department) {
            return [];
        }

        $departmentRequiredRoles = [
            'recursos humanos' => ['Employee'],
            'ti' => ['Employee'],
            // Add other department requirements here
        ];

        return $departmentRequiredRoles[strtolower($department)] ?? [];
    }

    /**
     * Get roles that depend on this role
     */
    protected function getDependentRoles(string $roleName): array
    {
        $dependencyRules = [
            'Employee' => ['Manager'], // Manager depends on Employee
            // Add other dependency rules here
        ];

        return $dependencyRules[$roleName] ?? [];
    }

    /**
     * Validate bulk role assignment
     */
    public function validateBulkRoleAssignment(array $userIds, array $roleNames): array
    {
        $results = [
            'valid' => [],
            'invalid' => []
        ];

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if (!$user) {
                $results['invalid'][$userId] = ['Utilizador não encontrado'];
                continue;
            }

            $userErrors = [];
            foreach ($roleNames as $roleName) {
                $roleErrors = $this->validateRoleAssignment($user, $roleName);
                if (!empty($roleErrors)) {
                    $userErrors[$roleName] = $roleErrors;
                }
            }

            if (empty($userErrors)) {
                $results['valid'][] = $userId;
            } else {
                $results['invalid'][$userId] = $userErrors;
            }
        }

        return $results;
    }

    /**
     * Get role compatibility matrix
     */
    public function getRoleCompatibilityMatrix(): array
    {
        $roles = Role::all()->pluck('name')->toArray();
        $matrix = [];

        foreach ($roles as $role1) {
            foreach ($roles as $role2) {
                if ($role1 === $role2) {
                    $matrix[$role1][$role2] = 'same';
                    continue;
                }

                $exclusiveRoles = $this->getMutuallyExclusiveRoles($role1);
                if (in_array($role2, $exclusiveRoles)) {
                    $matrix[$role1][$role2] = 'exclusive';
                } else {
                    $matrix[$role1][$role2] = 'compatible';
                }
            }
        }

        return $matrix;
    }
}
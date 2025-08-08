<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Services\PermissionService;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'model_type'
    ];

    /**
     * The groups that have this permission.
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class, 'group_permission', 'permission_id', 'group_id')
            ->withTimestamps();
    }

    /**
     * Create a new permission if it doesn't exist
     *
     * @param string $name
     * @param string $slug
     * @param string|null $description
     * @param string|null $modelType
     * @return static
     */
    public static function findOrCreate(
        string $name,
        string $slug,
        ?string $description = null,
        ?string $modelType = null
    ): self {
        return static::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'description' => $description,
                'model_type' => $modelType
            ]
        );
    }

    /**
     * Get all permissions for a specific model type
     *
     * @param string $modelType
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByModelType(string $modelType)
    {
        return static::where('model_type', $modelType)->get();
    }

    /**
     * Clear permission cache for all groups that have this permission
     */
    public function clearPermissionCache(): void
    {
        $permissionService = app(PermissionService::class);
        
        // Clear cache for all groups that have this permission
        $this->groups->each(function ($group) use ($permissionService) {
            $permissionService->clearGroupCache($group->id);
        });
    }
}

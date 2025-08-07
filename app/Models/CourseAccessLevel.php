<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseAccessLevel extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'level',
        'description'
    ];

    protected $casts = [
        'level' => 'integer'
    ];

    /**
     * Get the access rules for this level
     */
    public function accessRules(): HasMany
    {
        return $this->hasMany(CourseAccessRule::class, 'access_level_id');
    }

    /**
     * Get the user skill levels for this access level
     */
    public function userSkillLevels(): HasMany
    {
        return $this->hasMany(UserSkillLevel::class, 'access_level_id');
    }

    /**
     * Find or create an access level by slug
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Get the default access levels
     */
    public static function getDefaultLevels(): array
    {
        return [
            [
                'name' => 'Beginner',
                'slug' => 'beginner',
                'level' => 1,
                'description' => 'Basic level access'
            ],
            [
                'name' => 'Intermediate',
                'slug' => 'intermediate',
                'level' => 2,
                'description' => 'Intermediate level access'
            ],
            [
                'name' => 'Advanced',
                'slug' => 'advanced',
                'level' => 3,
                'description' => 'Advanced level access'
            ],
            [
                'name' => 'Expert',
                'slug' => 'expert',
                'level' => 4,
                'description' => 'Expert level access'
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'level' => 100,
                'description' => 'Administrative access'
            ]
        ];
    }

    /**
     * Check if a user has at least this access level for a skill
     */
    public function userHasAccess(User $user, string $skillName): bool
    {
        $userSkill = UserSkillLevel::where('user_id', $user->id)
            ->where('skill_name', $skillName)
            ->first();

        if (!$userSkill) {
            return false;
        }

        return $userSkill->accessLevel->level >= $this->level;
    }
}

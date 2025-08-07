<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'title',
        'description',
        'category_id',
        'difficulty_level',
        'estimated_duration',
        'thumbnail',
        'is_active',
        'order_index',
        'metadata'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order_index' => 'integer',
        'estimated_duration' => 'integer',
        'metadata' => 'array'
    ];

    // Relacionamentos
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class)->orderBy('order_index');
    }

    public function progress(): HasMany
    {
        return $this->hasMany(CourseProgress::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByDifficulty($query, $level)
    {
        return $query->where('difficulty_level', $level);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index');
    }

    public function scopeWithCategory($query)
    {
        return $query->with('category');
    }

    // Accessors
    public function getActiveVideosCountAttribute(): int
    {
        return $this->videos()->where('is_active', true)->count();
    }

    public function getTotalDurationAttribute(): int
    {
        return $this->videos()
            ->where('is_active', true)
            ->sum('duration') ?? 0;
    }

    public function getCompletionRateAttribute(): float
    {
        $totalUsers = $this->progress()->distinct('user_id')->count();
        if ($totalUsers === 0) return 0;
        
        $completedUsers = $this->progress()
            ->where('progress_percentage', 100)
            ->distinct('user_id')
            ->count();
            
        return round(($completedUsers / $totalUsers) * 100, 2);
    }

    public function getDifficultyBadgeAttribute(): string
    {
        return match($this->difficulty_level) {
            'beginner' => 'Iniciante',
            'intermediate' => 'Intermediário', 
            'advanced' => 'Avançado',
            default => 'Não definido'
        };
    }
}
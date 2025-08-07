<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'description',
        'icon',
        'color',
        'order_index',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order_index' => 'integer'
    ];

    // Relacionamentos
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class)->orderBy('order_index');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index');
    }

    // Accessors
    public function getActiveCoursesCountAttribute(): int
    {
        return $this->courses()->where('is_active', true)->count();
    }

    public function getTotalVideosCountAttribute(): int
    {
        return $this->courses()
            ->where('is_active', true)
            ->withCount(['videos' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get()
            ->sum('videos_count');
    }
}
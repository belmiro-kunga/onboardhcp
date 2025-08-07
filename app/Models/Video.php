<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Video extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'course_id',
        'title',
        'description',
        'source_type',
        'source_url',
        'duration',
        'thumbnail',
        'metadata',
        'order_index',
        'is_active',
        'processed_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order_index' => 'integer',
        'duration' => 'integer',
        'metadata' => 'array',
        'processed_at' => 'datetime'
    ];

    // Relacionamentos
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
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

    public function scopeBySourceType($query, $type)
    {
        return $query->where('source_type', $type);
    }

    public function scopeProcessed($query)
    {
        return $query->whereNotNull('processed_at');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index');
    }

    public function scopeWithCourse($query)
    {
        return $query->with('course');
    }

    // Accessors
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration) return 'N/A';
        
        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        $seconds = $this->duration % 60;
        
        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }
        
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function getIsProcessedAttribute(): bool
    {
        return !is_null($this->processed_at);
    }

    public function getSourceTypeIconAttribute(): string
    {
        return match($this->source_type) {
            'youtube' => 'fab fa-youtube',
            'local' => 'fas fa-file-video',
            'r2' => 'fas fa-cloud',
            default => 'fas fa-video'
        };
    }

    public function getWatchCountAttribute(): int
    {
        return $this->progress()->distinct('user_id')->count();
    }

    public function getCompletionRateAttribute(): float
    {
        $totalWatchers = $this->progress()->distinct('user_id')->count();
        if ($totalWatchers === 0) return 0;
        
        $completedWatchers = $this->progress()
            ->where('progress_percentage', 100)
            ->distinct('user_id')
            ->count();
            
        return round(($completedWatchers / $totalWatchers) * 100, 2);
    }
}
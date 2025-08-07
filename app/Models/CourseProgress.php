<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\User\Models\User;

class CourseProgress extends Model
{
    use HasFactory;
    
    protected $table = 'course_progress';
    
    protected $fillable = [
        'user_id',
        'course_id',
        'video_id',
        'progress_percentage',
        'completed_at',
        'last_watched_at',
        'watch_time_seconds'
    ];

    protected $casts = [
        'progress_percentage' => 'decimal:2',
        'watch_time_seconds' => 'integer',
        'completed_at' => 'datetime',
        'last_watched_at' => 'datetime'
    ];

    // Relacionamentos
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('progress_percentage', 100);
    }

    public function scopeInProgress($query)
    {
        return $query->where('progress_percentage', '>', 0)
                    ->where('progress_percentage', '<', 100);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeByVideo($query, $videoId)
    {
        return $query->where('video_id', $videoId);
    }

    public function scopeRecentlyWatched($query, $days = 7)
    {
        return $query->where('last_watched_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getIsCompletedAttribute(): bool
    {
        return $this->progress_percentage >= 100;
    }

    public function getFormattedWatchTimeAttribute(): string
    {
        if (!$this->watch_time_seconds) return '0s';
        
        $hours = floor($this->watch_time_seconds / 3600);
        $minutes = floor(($this->watch_time_seconds % 3600) / 60);
        $seconds = $this->watch_time_seconds % 60;
        
        if ($hours > 0) {
            return sprintf('%dh %dm %ds', $hours, $minutes, $seconds);
        } elseif ($minutes > 0) {
            return sprintf('%dm %ds', $minutes, $seconds);
        }
        
        return sprintf('%ds', $seconds);
    }

    public function getStatusAttribute(): string
    {
        if ($this->progress_percentage >= 100) {
            return 'Concluído';
        } elseif ($this->progress_percentage > 0) {
            return 'Em Progresso';
        }
        
        return 'Não Iniciado';
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->progress_percentage >= 100) {
            return 'text-green-600';
        } elseif ($this->progress_percentage > 0) {
            return 'text-yellow-600';
        }
        
        return 'text-gray-400';
    }

    // Métodos auxiliares
    public function markAsCompleted(): void
    {
        $this->update([
            'progress_percentage' => 100,
            'completed_at' => now(),
            'last_watched_at' => now()
        ]);
    }

    public function updateProgress(float $percentage, int $watchTime = null): void
    {
        $data = [
            'progress_percentage' => min(100, max(0, $percentage)),
            'last_watched_at' => now()
        ];
        
        if ($watchTime !== null) {
            $data['watch_time_seconds'] = $watchTime;
        }
        
        if ($percentage >= 100) {
            $data['completed_at'] = now();
        }
        
        $this->update($data);
    }
}
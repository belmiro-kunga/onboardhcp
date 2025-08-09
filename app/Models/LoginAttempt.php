<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LoginAttempt extends Model
{
    use HasFactory;

    protected $table = 'login_attempts';
    
    protected $fillable = [
        'email',
        'ip_address',
        'success'
    ];

    protected $casts = [
        'success' => 'boolean',
        'attempted_at' => 'datetime'
    ];

    public $timestamps = false; // Only using attempted_at

    // Scopes
    public function scopeForEmail($query, string $email)
    {
        return $query->where('email', $email);
    }

    public function scopeForIp($query, string $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    public function scopeRecent($query, int $minutes = 60)
    {
        return $query->where('attempted_at', '>=', now()->subMinutes($minutes));
    }

    // Helper methods
    public static function recordAttempt(string $email, bool $success): void
    {
        self::create([
            'email' => $email,
            'ip_address' => request()->ip(),
            'success' => $success,
            'attempted_at' => now()
        ]);
    }

    public static function getFailedAttemptsCount(string $email, int $minutes = 60): int
    {
        return self::forEmail($email)
            ->failed()
            ->recent($minutes)
            ->count();
    }

    public static function getFailedAttemptsCountByIp(string $ipAddress, int $minutes = 60): int
    {
        return self::forIp($ipAddress)
            ->failed()
            ->recent($minutes)
            ->count();
    }

    public static function hasRecentSuccessfulLogin(string $email, int $minutes = 5): bool
    {
        return self::forEmail($email)
            ->successful()
            ->recent($minutes)
            ->exists();
    }

    public static function clearOldAttempts(int $days = 30): void
    {
        self::where('attempted_at', '<', now()->subDays($days))->delete();
    }
}

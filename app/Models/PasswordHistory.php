<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;

class PasswordHistory extends Model
{
    use HasFactory;

    protected $table = 'password_history';
    
    protected $fillable = [
        'user_id',
        'password_hash'
    ];

    protected $casts = [
        'created_at' => 'datetime'
    ];

    public $timestamps = false; // Only using created_at

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, int $count = 5)
    {
        return $query->orderBy('created_at', 'desc')->limit($count);
    }

    // Helper methods
    public static function addPasswordToHistory(int $userId, string $password): void
    {
        self::create([
            'user_id' => $userId,
            'password_hash' => Hash::make($password),
            'created_at' => now()
        ]);
    }

    public static function isPasswordReused(int $userId, string $password, int $historyCount = 5): bool
    {
        $recentPasswords = self::forUser($userId)
            ->recent($historyCount)
            ->pluck('password_hash');

        foreach ($recentPasswords as $hashedPassword) {
            if (Hash::check($password, $hashedPassword)) {
                return true;
            }
        }

        return false;
    }

    public static function cleanOldPasswords(int $userId, int $keepCount = 5): void
    {
        $passwordsToDelete = self::forUser($userId)
            ->orderBy('created_at', 'desc')
            ->skip($keepCount)
            ->pluck('id');

        if ($passwordsToDelete->isNotEmpty()) {
            self::whereIn('id', $passwordsToDelete)->delete();
        }
    }
}

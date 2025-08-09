<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'changes',
        'ip_address',
        'user_agent',
        'performed_by',
        'performed_at'
    ];

    protected $casts = [
        'changes' => 'array',
        'performed_at' => 'datetime'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // Scopes
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByPerformer($query, int $performerId)
    {
        return $query->where('performed_by', $performerId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('performed_at', '>=', now()->subDays($days));
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('performed_at', [$startDate, $endDate]);
    }

    // Helper methods
    public static function logAction(
        string $action,
        ?int $userId = null,
        array $changes = [],
        ?int $performedBy = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'action' => $action,
            'changes' => $changes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_by' => $performedBy ?? auth()->id(),
            'performed_at' => now()
        ]);
    }

    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'user_created' => 'Utilizador Criado',
            'user_updated' => 'Utilizador Atualizado',
            'user_deleted' => 'Utilizador Eliminado',
            'user_activated' => 'Utilizador Ativado',
            'user_deactivated' => 'Utilizador Desativado',
            'role_assigned' => 'Role Atribuído',
            'role_removed' => 'Role Removido',
            'password_reset' => 'Password Redefinida',
            'login_success' => 'Login Bem-sucedido',
            'login_failed' => 'Login Falhado',
            'bulk_action' => 'Ação em Lote',
            default => ucfirst(str_replace('_', ' ', $this->action))
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\User\Models\User;

class UserActivity extends Model
{
    protected $fillable = [
        'user_id',
        'activity_type',
        'activity_description',
        'ip_address',
        'user_agent',
        'session_id',
        'url',
        'method',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime'
    ];

    public $timestamps = false;

    /**
     * User relationship
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Activity type constants
     */
    const TYPE_LOGIN = 'login';
    const TYPE_LOGOUT = 'logout';
    const TYPE_PAGE_VIEW = 'page_view';
    const TYPE_ACTION = 'action';
    const TYPE_FORM_SUBMIT = 'form_submit';
    const TYPE_FILE_UPLOAD = 'file_upload';
    const TYPE_FILE_DOWNLOAD = 'file_download';
    const TYPE_SEARCH = 'search';
    const TYPE_EXPORT = 'export';
    const TYPE_IMPORT = 'import';
    const TYPE_STATUS_CHANGE = 'status_change';
    const TYPE_PROFILE_UPDATE = 'profile_update';
    const TYPE_PASSWORD_CHANGE = 'password_change';

    /**
     * Get activity type label
     */
    public function getActivityTypeLabel(): string
    {
        $labels = [
            self::TYPE_LOGIN => '🔐 Login',
            self::TYPE_LOGOUT => '🚪 Logout',
            self::TYPE_PAGE_VIEW => '👁️ Visualização de Página',
            self::TYPE_ACTION => '⚡ Ação',
            self::TYPE_FORM_SUBMIT => '📝 Envio de Formulário',
            self::TYPE_FILE_UPLOAD => '📤 Upload de Arquivo',
            self::TYPE_FILE_DOWNLOAD => '📥 Download de Arquivo',
            self::TYPE_SEARCH => '🔍 Pesquisa',
            self::TYPE_EXPORT => '📊 Exportação',
            self::TYPE_IMPORT => '📥 Importação',
            self::TYPE_STATUS_CHANGE => '🔄 Alteração de Status',
            self::TYPE_PROFILE_UPDATE => '👤 Atualização de Perfil',
            self::TYPE_PASSWORD_CHANGE => '🔑 Alteração de Senha'
        ];

        return $labels[$this->activity_type] ?? $this->activity_type;
    }

    /**
     * Get activity icon
     */
    public function getActivityIcon(): string
    {
        $icons = [
            self::TYPE_LOGIN => '🔐',
            self::TYPE_LOGOUT => '🚪',
            self::TYPE_PAGE_VIEW => '👁️',
            self::TYPE_ACTION => '⚡',
            self::TYPE_FORM_SUBMIT => '📝',
            self::TYPE_FILE_UPLOAD => '📤',
            self::TYPE_FILE_DOWNLOAD => '📥',
            self::TYPE_SEARCH => '🔍',
            self::TYPE_EXPORT => '📊',
            self::TYPE_IMPORT => '📥',
            self::TYPE_STATUS_CHANGE => '🔄',
            self::TYPE_PROFILE_UPDATE => '👤',
            self::TYPE_PASSWORD_CHANGE => '🔑'
        ];

        return $icons[$this->activity_type] ?? '📋';
    }

    /**
     * Get formatted time ago
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get browser from user agent
     */
    public function getBrowserAttribute(): string
    {
        if (!$this->user_agent) {
            return 'Desconhecido';
        }

        $userAgent = $this->user_agent;
        
        if (strpos($userAgent, 'Chrome') !== false) {
            return 'Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            return 'Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            return 'Safari';
        } elseif (strpos($userAgent, 'Edge') !== false) {
            return 'Edge';
        } elseif (strpos($userAgent, 'Opera') !== false) {
            return 'Opera';
        }

        return 'Outro';
    }

    /**
     * Get device type from user agent
     */
    public function getDeviceTypeAttribute(): string
    {
        if (!$this->user_agent) {
            return 'Desconhecido';
        }

        $userAgent = strtolower($this->user_agent);
        
        if (strpos($userAgent, 'mobile') !== false || strpos($userAgent, 'android') !== false || strpos($userAgent, 'iphone') !== false) {
            return '📱 Mobile';
        } elseif (strpos($userAgent, 'tablet') !== false || strpos($userAgent, 'ipad') !== false) {
            return '📱 Tablet';
        }

        return '💻 Desktop';
    }

    /**
     * Scope for recent activities
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for activity type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Scope for user activities
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}

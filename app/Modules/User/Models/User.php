<?php

namespace App\Modules\User\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'birth_date',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
            'is_admin' => 'boolean',
        ];
    }

    // Scopes para consultas comuns
    public function scopeAdmins($query)
    {
        return $query->where('is_admin', true);
    }

    public function scopeEmployees($query)
    {
        return $query->where('is_admin', false);
    }

    public function scopeWithBirthdate($query)
    {
        return $query->whereNotNull('birth_date');
    }

    // Accessors
    public function getIsAdminAttribute($value): bool
    {
        return (bool) $value;
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date ? $this->birth_date->age : null;
    }
}
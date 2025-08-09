<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bio',
        'skills',
        'preferences',
        'emergency_contact'
    ];

    protected $casts = [
        'skills' => 'array',
        'preferences' => 'array',
        'emergency_contact' => 'array'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods
    public function addSkill(string $skill): void
    {
        $skills = $this->skills ?? [];
        if (!in_array($skill, $skills)) {
            $skills[] = $skill;
            $this->update(['skills' => $skills]);
        }
    }

    public function removeSkill(string $skill): void
    {
        $skills = $this->skills ?? [];
        $skills = array_filter($skills, fn($s) => $s !== $skill);
        $this->update(['skills' => array_values($skills)]);
    }

    public function setPreference(string $key, $value): void
    {
        $preferences = $this->preferences ?? [];
        $preferences[$key] = $value;
        $this->update(['preferences' => $preferences]);
    }

    public function getPreference(string $key, $default = null)
    {
        return $this->preferences[$key] ?? $default;
    }

    public function setEmergencyContact(array $contact): void
    {
        $this->update(['emergency_contact' => $contact]);
    }
}

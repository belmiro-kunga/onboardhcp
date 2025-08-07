<?php

namespace App\Modules\Simulado\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\User\Models\User;

class SimuladoTentativa extends Model
{
    protected $fillable = [
        'user_id',
        'simulado_id',
        'respostas',
        'pontuacao',
        'percentual',
        'aprovado',
        'iniciado_em',
        'finalizado_em',
        'tempo_gasto_segundos'
    ];

    protected $casts = [
        'respostas' => 'array',
        'pontuacao' => 'integer',
        'percentual' => 'decimal:2',
        'aprovado' => 'boolean',
        'iniciado_em' => 'datetime',
        'finalizado_em' => 'datetime',
        'tempo_gasto_segundos' => 'integer'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function simulado(): BelongsTo
    {
        return $this->belongsTo(Simulado::class);
    }

    public function getTempoGastoFormatadoAttribute(): string
    {
        if (!$this->tempo_gasto_segundos) return '00:00';
        
        $minutos = floor($this->tempo_gasto_segundos / 60);
        $segundos = $this->tempo_gasto_segundos % 60;
        
        return sprintf('%02d:%02d', $minutos, $segundos);
    }
}
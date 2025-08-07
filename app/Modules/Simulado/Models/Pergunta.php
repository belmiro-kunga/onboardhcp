<?php

namespace App\Modules\Simulado\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pergunta extends Model
{
    protected $fillable = [
        'simulado_id',
        'pergunta',
        'tipo',
        'opcoes',
        'respostas_corretas',
        'explicacao',
        'video_url',
        'ordem'
    ];

    protected $casts = [
        'opcoes' => 'array',
        'respostas_corretas' => 'array',
        'ordem' => 'integer'
    ];

    public function simulado(): BelongsTo
    {
        return $this->belongsTo(Simulado::class);
    }

    public function isRespostaCorreta(array $respostaUsuario): bool
    {
        sort($respostaUsuario);
        $respostasCorretas = $this->respostas_corretas;
        sort($respostasCorretas);
        
        return $respostaUsuario === $respostasCorretas;
    }
}
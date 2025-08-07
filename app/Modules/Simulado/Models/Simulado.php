<?php

namespace App\Modules\Simulado\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\User\Models\User;

class Simulado extends Model
{
    protected $fillable = [
        'titulo',
        'descricao',
        'duracao_minutos',
        'nota_aprovacao',
        'ativo'
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'nota_aprovacao' => 'integer',
        'duracao_minutos' => 'integer'
    ];

    public function perguntas(): HasMany
    {
        return $this->hasMany(Pergunta::class)->orderBy('ordem');
    }

    public function tentativas(): HasMany
    {
        return $this->hasMany(SimuladoTentativa::class);
    }

    public function getTotalPerguntasAttribute(): int
    {
        return $this->perguntas()->count();
    }

    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    // Adicionar este método como alias
    public function scopeAtivo($query)
    {
        return $this->scopeAtivos($query);
    }
}
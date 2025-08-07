<?php

namespace App\Modules\Simulado\Services;

use App\Modules\Simulado\Models\Simulado;
use App\Modules\Simulado\Models\Pergunta;
use App\Modules\Simulado\Models\SimuladoTentativa;
use App\Modules\User\Models\User;
use Carbon\Carbon;

class SimuladoService
{
    public function getAllSimulados()
    {
        return Simulado::with('perguntas')->orderBy('created_at', 'desc')->get();
    }

    public function getSimuladosAtivos()
    {
        return Simulado::ativos()->with('perguntas')->orderBy('titulo')->get();
    }

    public function getSimuladoById($id)
    {
        return Simulado::with('perguntas')->findOrFail($id);
    }

    public function createSimulado(array $data)
    {
        return Simulado::create($data);
    }

    public function updateSimulado(Simulado $simulado, array $data)
    {
        $simulado->update($data);
        return $simulado;
    }

    public function deleteSimulado(Simulado $simulado)
    {
        return $simulado->delete();
    }

    public function addPergunta(Simulado $simulado, array $data)
    {
        $data['simulado_id'] = $simulado->id;
        $data['ordem'] = $simulado->perguntas()->max('ordem') + 1;
        
        return Pergunta::create($data);
    }

    public function iniciarSimulado(User $user, Simulado $simulado)
    {
        return SimuladoTentativa::create([
            'user_id' => $user->id,
            'simulado_id' => $simulado->id,
            'respostas' => [],
            'pontuacao' => 0,
            'percentual' => 0,
            'aprovado' => false,
            'iniciado_em' => Carbon::now()
        ]);
    }

    public function finalizarSimulado(SimuladoTentativa $tentativa, array $respostas)
    {
        $simulado = $tentativa->simulado;
        $perguntas = $simulado->perguntas;
        
        $pontuacao = 0;
        $totalPerguntas = $perguntas->count();
        
        foreach ($perguntas as $pergunta) {
            $respostaUsuario = $respostas[$pergunta->id] ?? [];
            if ($pergunta->isRespostaCorreta($respostaUsuario)) {
                $pontuacao++;
            }
        }
        
        $percentual = $totalPerguntas > 0 ? ($pontuacao / $totalPerguntas) * 100 : 0;
        $aprovado = $percentual >= $simulado->nota_aprovacao;
        
        $tempoGasto = Carbon::now()->diffInSeconds($tentativa->iniciado_em);
        
        $tentativa->update([
            'respostas' => $respostas,
            'pontuacao' => $pontuacao,
            'percentual' => $percentual,
            'aprovado' => $aprovado,
            'finalizado_em' => Carbon::now(),
            'tempo_gasto_segundos' => $tempoGasto
        ]);
        
        return $tentativa;
    }

    public function getResultadoDetalhado(SimuladoTentativa $tentativa)
    {
        $simulado = $tentativa->simulado;
        $perguntas = $simulado->perguntas;
        $respostasUsuario = $tentativa->respostas;
        
        $detalhes = [];
        
        foreach ($perguntas as $pergunta) {
            $respostaUsuario = $respostasUsuario[$pergunta->id] ?? [];
            $correta = $pergunta->isRespostaCorreta($respostaUsuario);
            
            $detalhes[] = [
                'pergunta' => $pergunta,
                'resposta_usuario' => $respostaUsuario,
                'resposta_correta' => $pergunta->respostas_corretas,
                'correta' => $correta,
                'explicacao' => $pergunta->explicacao,
                'video_url' => $pergunta->video_url
            ];
        }
        
        return $detalhes;
    }

    public function getTentativasUsuario(User $user, Simulado $simulado = null)
    {
        $query = SimuladoTentativa::where('user_id', $user->id)
            ->with(['simulado'])
            ->whereNotNull('finalizado_em')
            ->orderBy('created_at', 'desc');
            
        if ($simulado) {
            $query->where('simulado_id', $simulado->id);
        }
        
        return $query->get();
    }

    public function getEstatisticas()
    {
        return [
            'total_simulados' => Simulado::count(),
            'simulados_ativos' => Simulado::ativos()->count(),
            'total_tentativas' => SimuladoTentativa::whereNotNull('finalizado_em')->count(),
            'taxa_aprovacao' => $this->getTaxaAprovacao()
        ];
    }

    public function getTotalSimuladosCount(): int
    {
        return Simulado::count();
    }

    public function getActiveSimuladosCount(): int
    {
        return Simulado::ativos()->count();
    }

    private function getTaxaAprovacao()
    {
        $totalTentativas = SimuladoTentativa::whereNotNull('finalizado_em')->count();
        $tentativasAprovadas = SimuladoTentativa::whereNotNull('finalizado_em')
            ->where('aprovado', true)->count();
            
        return $totalTentativas > 0 ? ($tentativasAprovadas / $totalTentativas) * 100 : 0;
    }
}
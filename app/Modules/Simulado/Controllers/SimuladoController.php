<?php

namespace App\Modules\Simulado\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Simulado\Services\SimuladoService;
use App\Modules\Simulado\Models\SimuladoTentativa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SimuladoController extends Controller
{
    protected $simuladoService;

    public function __construct(SimuladoService $simuladoService)
    {
        $this->simuladoService = $simuladoService;
        $this->middleware('auth');
    }

    public function index()
    {
        $simulados = $this->simuladoService->getSimuladosAtivos();
        $minhasTentativas = $this->simuladoService->getTentativasUsuario(Auth::user());
        
        return view('funcionario.simulados.index', compact('simulados', 'minhasTentativas'));
    }

    public function show($id)
    {
        $simulado = $this->simuladoService->getSimuladoById($id);
        return view('funcionario.simulados.show', compact('simulado'));
    }

    public function iniciar($id)
    {
        $simulado = $this->simuladoService->getSimuladoById($id);
        $tentativa = $this->simuladoService->iniciarSimulado(Auth::user(), $simulado);
        
        return redirect()->route('simulados.executar', $tentativa->id);
    }

    public function executar($tentativaId)
    {
        $tentativa = SimuladoTentativa::with(['simulado.perguntas'])->findOrFail($tentativaId);
        
        // Verificar se a tentativa pertence ao usuário logado
        if ($tentativa->user_id !== Auth::id()) {
            abort(403);
        }
        
        // Verificar se já foi finalizada
        if ($tentativa->finalizado_em) {
            return redirect()->route('simulados.resultado', $tentativa->id);
        }
        
        return view('funcionario.simulados.executar', compact('tentativa'));
    }

    public function finalizar(Request $request, $tentativaId)
    {
        $tentativa = SimuladoTentativa::findOrFail($tentativaId);
        
        if ($tentativa->user_id !== Auth::id()) {
            abort(403);
        }
        
        $respostas = $request->input('respostas', []);
        $tentativa = $this->simuladoService->finalizarSimulado($tentativa, $respostas);
        
        return redirect()->route('simulados.resultado', $tentativa->id);
    }

    public function resultado($tentativaId)
    {
        $tentativa = SimuladoTentativa::with(['simulado'])->findOrFail($tentativaId);
        
        if ($tentativa->user_id !== Auth::id()) {
            abort(403);
        }
        
        $detalhes = $this->simuladoService->getResultadoDetalhado($tentativa);
        
        return view('funcionario.simulados.resultado', compact('tentativa', 'detalhes'));
    }
}
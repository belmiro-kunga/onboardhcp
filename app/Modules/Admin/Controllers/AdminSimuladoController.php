<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Simulado\Services\SimuladoService;
use App\Modules\Simulado\Models\Simulado;
use App\Modules\Simulado\Models\Pergunta;
use Illuminate\Http\Request;

class AdminSimuladoController extends Controller
{
    protected $simuladoService;

    public function __construct(SimuladoService $simuladoService)
    {
        $this->simuladoService = $simuladoService;
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $simulados = $this->simuladoService->getAllSimulados();
        $estatisticas = $this->simuladoService->getEstatisticas();
        
        return view('admin.simulados.index', compact('simulados', 'estatisticas'));
    }

    public function create()
    {
        return view('admin.simulados.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'duracao_minutos' => 'required|integer|min:1',
            'nota_aprovacao' => 'required|integer|min:1|max:100',
            'ativo' => 'boolean'
        ]);

        $simulado = $this->simuladoService->createSimulado($validatedData);

        if ($request->has('continue_to_questions')) {
            return redirect()->route('admin.simulados.questions.create', $simulado->id)
                ->with('success', 'Simulado criado! Agora adicione as perguntas.');
        }

        return redirect()->route('admin.simulados')
            ->with('success', 'Simulado criado com sucesso!');
    }

    public function show($id)
    {
        $simulado = $this->simuladoService->getSimuladoById($id);
        return view('admin.simulados.show', compact('simulado'));
    }

    public function edit($id)
    {
        $simulado = $this->simuladoService->getSimuladoById($id);
        return view('admin.simulados.edit', compact('simulado'));
    }

    public function update(Request $request, $id)
    {
        $simulado = $this->simuladoService->getSimuladoById($id);
        
        $validatedData = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'duracao_minutos' => 'required|integer|min:1',
            'nota_aprovacao' => 'required|integer|min:1|max:100',
            'ativo' => 'boolean'
        ]);

        $this->simuladoService->updateSimulado($simulado, $validatedData);

        return redirect()->route('admin.simulados')
            ->with('success', 'Simulado atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $simulado = $this->simuladoService->getSimuladoById($id);
        $this->simuladoService->deleteSimulado($simulado);

        return redirect()->route('admin.simulados')
            ->with('success', 'Simulado eliminado com sucesso!');
    }

    // Wizard - Step 1: Informações Básicas
    public function wizard()
    {
        return view('admin.simulados.wizard.step1');
    }

    public function wizardStep1(Request $request)
    {
        $validatedData = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'duracao_minutos' => 'required|integer|min:1',
            'nota_aprovacao' => 'required|integer|min:1|max:100',
        ]);

        // Armazenar dados na sessão
        session(['simulado_wizard' => $validatedData]);

        return redirect()->route('admin.simulados.wizard.step2');
    }

    // Wizard - Step 2: Adicionar Perguntas
    public function wizardStep2()
    {
        $simuladoData = session('simulado_wizard');
        if (!$simuladoData) {
            return redirect()->route('admin.simulados.wizard')
                ->with('error', 'Dados do simulado não encontrados. Comece novamente.');
        }

        $perguntas = session('simulado_perguntas', []);
        return view('admin.simulados.wizard.step2', compact('simuladoData', 'perguntas'));
    }

    public function wizardAddPergunta(Request $request)
    {
        $validatedData = $request->validate([
            'pergunta' => 'required|string',
            'tipo' => 'required|in:multipla_escolha,escolha_unica',
            'opcoes' => 'required|array|min:2',
            'opcoes.*' => 'required|string',
            'respostas_corretas' => 'required|array|min:1',
            'explicacao' => 'nullable|string',
            'video_url' => 'nullable|url'
        ]);

        $perguntas = session('simulado_perguntas', []);
        $validatedData['id'] = count($perguntas) + 1;
        $perguntas[] = $validatedData;
        
        session(['simulado_perguntas' => $perguntas]);

        return redirect()->route('admin.simulados.wizard.step2')
            ->with('success', 'Pergunta adicionada com sucesso!');
    }

    public function wizardRemovePergunta($index)
    {
        $perguntas = session('simulado_perguntas', []);
        if (isset($perguntas[$index])) {
            unset($perguntas[$index]);
            $perguntas = array_values($perguntas); // Reindexar array
            session(['simulado_perguntas' => $perguntas]);
        }

        return redirect()->route('admin.simulados.wizard.step2')
            ->with('success', 'Pergunta removida com sucesso!');
    }

    // Wizard - Step 3: Revisão e Finalização
    public function wizardStep3()
    {
        $simuladoData = session('simulado_wizard');
        $perguntas = session('simulado_perguntas', []);

        if (!$simuladoData) {
            return redirect()->route('admin.simulados.wizard')
                ->with('error', 'Dados do simulado não encontrados. Comece novamente.');
        }

        return view('admin.simulados.wizard.step3', compact('simuladoData', 'perguntas'));
    }

    public function wizardFinalize(Request $request)
    {
        // Verificar se é uma requisição AJAX (modal)
        if ($request->ajax() || $request->wantsJson()) {
            return $this->wizardFinalizeAjax($request);
        }

        // Processo original para wizard em páginas separadas
        $simuladoData = session('simulado_wizard');
        $perguntas = session('simulado_perguntas', []);

        if (!$simuladoData || empty($perguntas)) {
            return redirect()->route('admin.simulados.wizard')
                ->with('error', 'Dados incompletos. Comece novamente.');
        }

        // Criar o simulado
        $simuladoData['ativo'] = $request->has('ativo');
        $simulado = $this->simuladoService->createSimulado($simuladoData);

        // Adicionar as perguntas
        foreach ($perguntas as $index => $perguntaData) {
            unset($perguntaData['id']); // Remover ID temporário
            $perguntaData['ordem'] = $index + 1;
            $this->simuladoService->addPergunta($simulado, $perguntaData);
        }

        // Limpar sessão
        session()->forget(['simulado_wizard', 'simulado_perguntas']);

        return redirect()->route('admin.simulados')
            ->with('success', "Simulado '{$simulado->titulo}' criado com sucesso com " . count($perguntas) . " perguntas!");
    }

    private function wizardFinalizeAjax(Request $request)
    {
        try {
            // Validar dados do simulado
            $simuladoData = $request->validate([
                'titulo' => 'required|string|max:255',
                'descricao' => 'nullable|string',
                'duracao_minutos' => 'required|integer|min:1',
                'nota_aprovacao' => 'required|integer|min:1|max:100',
                'ativo' => 'boolean',
                'perguntas' => 'required|array|min:1',
                'perguntas.*.pergunta' => 'required|string',
                'perguntas.*.tipo' => 'required|in:escolha_unica,multipla_escolha',
                'perguntas.*.opcoes' => 'required|array|min:2',
                'perguntas.*.resposta_correta' => 'required|integer|min:0',
                'perguntas.*.explicacao' => 'nullable|string'
            ]);

            // Criar o simulado
            $simulado = $this->simuladoService->createSimulado([
                'titulo' => $simuladoData['titulo'],
                'descricao' => $simuladoData['descricao'],
                'duracao_minutos' => $simuladoData['duracao_minutos'],
                'nota_aprovacao' => $simuladoData['nota_aprovacao'],
                'ativo' => $simuladoData['ativo'] ?? false
            ]);

            // Adicionar as perguntas
            foreach ($simuladoData['perguntas'] as $index => $perguntaData) {
                $this->simuladoService->addPergunta($simulado, [
                    'pergunta' => $perguntaData['pergunta'],
                    'tipo' => $perguntaData['tipo'],
                    'opcoes' => $perguntaData['opcoes'],
                    'respostas_corretas' => [$perguntaData['resposta_correta']],
                    'explicacao' => $perguntaData['explicacao'],
                    'ordem' => $index + 1
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Simulado '{$simulado->titulo}' criado com sucesso com " . count($simuladoData['perguntas']) . " perguntas!",
                'simulado_id' => $simulado->id
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar simulado: ' . $e->getMessage()
            ], 500);
        }
    }

    // Gestão de Perguntas
    public function createQuestion($simuladoId)
    {
        $simulado = $this->simuladoService->getSimuladoById($simuladoId);
        return view('admin.simulados.questions.create', compact('simulado'));
    }

    public function storeQuestion(Request $request, $simuladoId)
    {
        $validatedData = $request->validate([
            'pergunta' => 'required|string',
            'tipo' => 'required|in:multipla_escolha,escolha_unica',
            'opcoes' => 'required|array|min:2',
            'opcoes.*' => 'required|string',
            'respostas_corretas' => 'required|array|min:1',
            'explicacao' => 'nullable|string',
            'video_url' => 'nullable|url'
        ]);

        $simulado = $this->simuladoService->getSimuladoById($simuladoId);
        $this->simuladoService->addPergunta($simulado, $validatedData);

        return redirect()->route('admin.simulados.show', $simuladoId)
            ->with('success', 'Pergunta adicionada com sucesso!');
    }

    public function editQuestion($simuladoId, $perguntaId)
    {
        $simulado = $this->simuladoService->getSimuladoById($simuladoId);
        $pergunta = Pergunta::findOrFail($perguntaId);
        
        return view('admin.simulados.questions.edit', compact('simulado', 'pergunta'));
    }

    public function updateQuestion(Request $request, $simuladoId, $perguntaId)
    {
        $validatedData = $request->validate([
            'pergunta' => 'required|string',
            'tipo' => 'required|in:multipla_escolha,escolha_unica',
            'opcoes' => 'required|array|min:2',
            'opcoes.*' => 'required|string',
            'respostas_corretas' => 'required|array|min:1',
            'explicacao' => 'nullable|string',
            'video_url' => 'nullable|url'
        ]);

        $pergunta = Pergunta::findOrFail($perguntaId);
        $pergunta->update($validatedData);

        return redirect()->route('admin.simulados.show', $simuladoId)
            ->with('success', 'Pergunta atualizada com sucesso!');
    }

    public function destroyQuestion($simuladoId, $perguntaId)
    {
        $pergunta = Pergunta::findOrFail($perguntaId);
        $pergunta->delete();

        return redirect()->route('admin.simulados.show', $simuladoId)
            ->with('success', 'Pergunta eliminada com sucesso!');
    }
}
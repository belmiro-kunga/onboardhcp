<?php

namespace App\Modules\Onboarding\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Onboarding\Services\OnboardingService;

class OnboardingController extends Controller
{
    protected $authService;
    protected $onboardingService;

    public function __construct(AuthService $authService, OnboardingService $onboardingService)
    {
        $this->authService = $authService;
        $this->onboardingService = $onboardingService;
        $this->middleware('auth');
    }

    public function index()
    {
        $user = $this->authService->user();
        $progress = $this->onboardingService->getUserProgress($user);
        $steps = $this->onboardingService->getOnboardingSteps();

        return view('funcionario.index', compact('user', 'progress', 'steps'));
    }

    public function completeStep(string $step)
    {
        $user = $this->authService->user();
        $this->onboardingService->completeStep($user, $step);

        return redirect()->route('funcionario')->with('success', 'Etapa concluída com sucesso!');
    }

    /**
     * Página principal do onboarding
     */
    public function onboardingIndex()
    {
        $user = $this->authService->user();
        return view('onboarding.index', compact('user'));
    }

    /**
     * Página de boas-vindas
     */
    public function boasVindas()
    {
        $user = $this->authService->user();
        return view('onboarding.boas-vindas', compact('user'));
    }

    /**
     * Página sobre a empresa
     */
    public function sobreEmpresa()
    {
        $user = $this->authService->user();
        
        $empresaInfo = [
            'nome' => 'Hemera Capital Partners',
            'fundacao' => '2015',
            'sede' => 'São Paulo, Brasil',
            'funcionarios' => '250+',
            'aum' => 'R$ 2.5 bilhões',
            'missao' => 'Gerar valor sustentável através de investimentos inteligentes e gestão de risco eficiente.',
            'visao' => 'Ser reconhecida como uma das principais gestoras de recursos do Brasil.',
            'valores' => [
                'Excelência operacional',
                'Transparência e ética',
                'Inovação constante',
                'Responsabilidade social',
                'Foco no cliente'
            ]
        ];

        return view('onboarding.sobre-empresa', compact('user', 'empresaInfo'));
    }

    /**
     * Página da história da empresa
     */
    public function historia()
    {
        $user = $this->authService->user();
        
        $timeline = [
            [
                'ano' => '2015',
                'titulo' => 'Fundação da Hemera Capital',
                'descricao' => 'Fundação da empresa por um grupo de profissionais experientes do mercado financeiro.',
                'marco' => 'Início das operações com R$ 50 milhões sob gestão.'
            ],
            [
                'ano' => '2017',
                'titulo' => 'Expansão de Produtos',
                'descricao' => 'Lançamento de novos fundos de investimento e diversificação do portfólio.',
                'marco' => 'Alcançamos R$ 500 milhões sob gestão.'
            ],
            [
                'ano' => '2019',
                'titulo' => 'Reconhecimento do Mercado',
                'descricao' => 'Prêmios de melhor gestora em categorias específicas.',
                'marco' => 'Ultrapassamos R$ 1 bilhão sob gestão.'
            ],
            [
                'ano' => '2021',
                'titulo' => 'Transformação Digital',
                'descricao' => 'Implementação de tecnologias avançadas e plataformas digitais.',
                'marco' => 'Lançamento da plataforma digital para clientes.'
            ],
            [
                'ano' => '2023',
                'titulo' => 'Sustentabilidade e ESG',
                'descricao' => 'Foco em investimentos sustentáveis e critérios ESG.',
                'marco' => 'Certificação como empresa B Corp.'
            ],
            [
                'ano' => '2025',
                'titulo' => 'Presente',
                'descricao' => 'Consolidação como uma das principais gestoras do país.',
                'marco' => 'R$ 2.5 bilhões sob gestão e 250+ funcionários.'
            ]
        ];

        return view('onboarding.historia', compact('user', 'timeline'));
    }

    /**
     * Página dos departamentos
     */
    public function departamentos()
    {
        $user = $this->authService->user();
        
        $departamentos = [
            [
                'nome' => 'Gestão de Investimentos',
                'descricao' => 'Responsável pela análise, seleção e gestão dos investimentos da carteira.',
                'responsabilidades' => [
                    'Análise de mercado e ativos',
                    'Gestão de portfólio',
                    'Research e due diligence',
                    'Estratégias de investimento'
                ],
                'equipe' => 45,
                'diretor' => 'Carlos Silva'
            ],
            [
                'nome' => 'Gestão de Risco',
                'descricao' => 'Monitora e controla os riscos associados aos investimentos e operações.',
                'responsabilidades' => [
                    'Análise de risco de crédito',
                    'Risco de mercado',
                    'Compliance regulatório',
                    'Stress testing'
                ],
                'equipe' => 25,
                'diretor' => 'Ana Santos'
            ],
            [
                'nome' => 'Relacionamento com Clientes',
                'descricao' => 'Atendimento e relacionamento com investidores e clientes institucionais.',
                'responsabilidades' => [
                    'Atendimento ao cliente',
                    'Captação de recursos',
                    'Relatórios para investidores',
                    'Eventos e apresentações'
                ],
                'equipe' => 30,
                'diretor' => 'Roberto Lima'
            ],
            [
                'nome' => 'Operações e Middle Office',
                'descricao' => 'Suporte operacional para as atividades de investimento e administração.',
                'responsabilidades' => [
                    'Liquidação de operações',
                    'Controle de posições',
                    'Conciliação contábil',
                    'Sistemas e tecnologia'
                ],
                'equipe' => 35,
                'diretor' => 'Mariana Costa'
            ],
            [
                'nome' => 'Recursos Humanos',
                'descricao' => 'Gestão de pessoas, desenvolvimento e cultura organizacional.',
                'responsabilidades' => [
                    'Recrutamento e seleção',
                    'Treinamento e desenvolvimento',
                    'Benefícios e remuneração',
                    'Cultura organizacional'
                ],
                'equipe' => 12,
                'diretor' => 'Patricia Oliveira'
            ],
            [
                'nome' => 'Financeiro e Controladoria',
                'descricao' => 'Controle financeiro, contabilidade e planejamento orçamentário.',
                'responsabilidades' => [
                    'Contabilidade geral',
                    'Planejamento financeiro',
                    'Controles internos',
                    'Relatórios gerenciais'
                ],
                'equipe' => 18,
                'diretor' => 'Fernando Alves'
            ]
        ];

        return view('onboarding.departamentos', compact('user', 'departamentos'));
    }

    /**
     * Página de cultura e valores
     */
    public function culturaValores()
    {
        $user = $this->authService->user();
        
        $cultura = [
            'principios' => [
                [
                    'titulo' => 'Excelência Operacional',
                    'descricao' => 'Buscamos sempre a melhor qualidade em tudo que fazemos.',
                    'icone' => '🎯'
                ],
                [
                    'titulo' => 'Transparência e Ética',
                    'descricao' => 'Agimos com integridade e transparência em todas as relações.',
                    'icone' => '🤝'
                ],
                [
                    'titulo' => 'Inovação Constante',
                    'descricao' => 'Estamos sempre buscando novas formas de melhorar nossos processos.',
                    'icone' => '💡'
                ],
                [
                    'titulo' => 'Responsabilidade Social',
                    'descricao' => 'Contribuímos para um mundo melhor através de nossos investimentos.',
                    'icone' => '🌱'
                ],
                [
                    'titulo' => 'Foco no Cliente',
                    'descricao' => 'O sucesso dos nossos clientes é a nossa prioridade.',
                    'icone' => '👥'
                ]
            ],
            'beneficios' => [
                'Plano de saúde e odontológico',
                'Vale refeição e alimentação',
                'Auxílio home office',
                'Programa de participação nos lucros',
                'Licença maternidade/paternidade estendida',
                'Programa de desenvolvimento profissional',
                'Horário flexível',
                'Trabalho híbrido'
            ]
        ];

        return view('onboarding.cultura-valores', compact('user', 'cultura'));
    }

    /**
     * Página do organograma
     */
    public function organograma()
    {
        $user = $this->authService->user();
        
        $organograma = [
            'ceo' => [
                'nome' => 'Eduardo Hemera',
                'cargo' => 'CEO & Fundador',
                'email' => 'eduardo.hemera@hemeracapital.com'
            ],
            'diretoria' => [
                [
                    'nome' => 'Carlos Silva',
                    'cargo' => 'Diretor de Investimentos',
                    'departamento' => 'Gestão de Investimentos',
                    'email' => 'carlos.silva@hemeracapital.com'
                ],
                [
                    'nome' => 'Ana Santos',
                    'cargo' => 'Diretora de Risco',
                    'departamento' => 'Gestão de Risco',
                    'email' => 'ana.santos@hemeracapital.com'
                ],
                [
                    'nome' => 'Roberto Lima',
                    'cargo' => 'Diretor Comercial',
                    'departamento' => 'Relacionamento com Clientes',
                    'email' => 'roberto.lima@hemeracapital.com'
                ],
                [
                    'nome' => 'Mariana Costa',
                    'cargo' => 'Diretora de Operações',
                    'departamento' => 'Operações e Middle Office',
                    'email' => 'mariana.costa@hemeracapital.com'
                ]
            ],
            'gerencias' => [
                [
                    'nome' => 'Patricia Oliveira',
                    'cargo' => 'Gerente de RH',
                    'departamento' => 'Recursos Humanos',
                    'email' => 'patricia.oliveira@hemeracapital.com'
                ],
                [
                    'nome' => 'Fernando Alves',
                    'cargo' => 'Gerente Financeiro',
                    'departamento' => 'Financeiro e Controladoria',
                    'email' => 'fernando.alves@hemeracapital.com'
                ]
            ]
        ];

        return view('onboarding.organograma', compact('user', 'organograma'));
    }
}
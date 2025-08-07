<?php

namespace App\Modules\Admin\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Modules\Auth\Services\AuthService;
use App\Modules\User\Services\UserService;
use App\Modules\Birthday\Services\BirthdayService;
use App\Modules\User\Models\User;

class AdminController extends Controller
{
    protected $authService;
    protected $userService;
    protected $birthdayService;

    public function __construct(
        AuthService $authService,
        UserService $userService,
        BirthdayService $birthdayService
    ) {
        $this->authService = $authService;
        $this->userService = $userService;
        $this->birthdayService = $birthdayService;
        $this->middleware(['auth', 'admin'])->except(['showLoginForm', 'login']);
    }

    public function showLoginForm()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($this->authService->attemptLogin($credentials)) {
            if ($this->authService->isAdmin()) {
                $request->session()->regenerate();
                return redirect()->intended('/admin/dashboard');
            } else {
                $this->authService->logout();
                return back()->withErrors([
                    'email' => 'Acesso negado. Esta área é restrita a administradores.',
                ])->onlyInput('email');
            }
        }

        return back()->withErrors([
            'email' => 'As credenciais fornecidas não correspondem aos nossos registos.',
        ])->onlyInput('email');
    }

    public function dashboard()
    {
        // User statistics
        $totalUsers = $this->userService->getTotalUsersCount();
        $totalAdmins = $this->userService->getAdminsCount();
        $activeUsers = $this->userService->getActiveUsersCount();
        
        // Birthday information
        $todayBirthdays = $this->birthdayService->getTodayBirthdaysCount();
        $upcomingBirthdays = $this->birthdayService->getUpcomingBirthdays(5);
        
        // Simulado statistics
        $simuladoService = app(\App\Modules\Simulado\Services\SimuladoService::class);
        $totalSimulados = $simuladoService->getTotalSimuladosCount();
        $simuladosAtivos = $simuladoService->getActiveSimuladosCount();
        
        // System metrics
        $systemMetrics = [
            'uptime' => 99.9,
            'performance' => 98.5,
            'storage_used' => 65.2,
            'memory_usage' => 42.8
        ];
        
        // Recent activity (mock data for now)
        $recentActivity = [
            [
                'type' => 'user_registered',
                'user' => 'João Silva',
                'timestamp' => now()->subMinutes(2),
                'description' => 'Novo utilizador registado'
            ],
            [
                'type' => 'simulado_completed',
                'user' => 'Maria Santos',
                'timestamp' => now()->subMinutes(15),
                'description' => 'Simulado completado'
            ],
            [
                'type' => 'system_update',
                'user' => 'Sistema',
                'timestamp' => now()->subHour(),
                'description' => 'Sistema atualizado para v1.2.0'
            ]
        ];

        return view('admin.dashboard', compact(
            'totalUsers', 
            'totalAdmins', 
            'activeUsers',
            'todayBirthdays', 
            'upcomingBirthdays',
            'totalSimulados',
            'simuladosAtivos',
            'systemMetrics',
            'recentActivity'
        ));
    }

    public function users()
    {
        $users = $this->userService->getAllUsers();
        return view('admin.users', compact('users'));
    }

    public function createUser(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'birth_date' => 'required|date',
            'is_admin' => 'boolean',
        ]);

        $this->userService->createUser($validatedData);

        return redirect()->route('admin.users')->with('success', 'Utilizador criado com sucesso!');
    }

    public function updateUser(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'birth_date' => 'required|date',
            'is_admin' => 'boolean',
            'password' => 'nullable|string|min:6',
        ]);

        $this->userService->updateUser($user, $validatedData);

        return redirect()->route('admin.users')->with('success', 'Utilizador actualizado com sucesso!');
    }

    public function deleteUser(User $user)
    {
        $this->userService->deleteUser($user);
        return redirect()->route('admin.users')->with('success', 'Utilizador eliminado com sucesso!');
    }

    public function simulados()
    {
        $simuladoService = app(\App\Modules\Simulado\Services\SimuladoService::class);
        $simulados = $simuladoService->getAllSimulados();
        $estatisticas = $simuladoService->getEstatisticas();
        
        return view('admin.simulados.index', compact('simulados', 'estatisticas'));
    }

    public function createSimulado(Request $request)
    {
        $validatedData = $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'duracao_minutos' => 'required|integer|min:1',
            'nota_aprovacao' => 'required|integer|min:1|max:100',
            'ativo' => 'boolean'
        ]);

        $simuladoService = app(\App\Modules\Simulado\Services\SimuladoService::class);
        $simuladoService->createSimulado($validatedData);

        return redirect()->route('admin.simulados')->with('success', 'Simulado criado com sucesso!');
    }

    public function showSimulado($id)
    {
        $simuladoService = app(\App\Modules\Simulado\Services\SimuladoService::class);
        $simulado = $simuladoService->getSimuladoById($id);
        
        return view('admin.simulados.show', compact('simulado'));
    }

    public function addPergunta(Request $request, $simuladoId)
    {
        $validatedData = $request->validate([
            'pergunta' => 'required|string',
            'tipo' => 'required|in:multipla_escolha,escolha_unica',
            'opcoes' => 'required|array|min:2',
            'respostas_corretas' => 'required|array|min:1',
            'explicacao' => 'nullable|string',
            'video_url' => 'nullable|url'
        ]);

        $simuladoService = app(\App\Modules\Simulado\Services\SimuladoService::class);
        $simulado = $simuladoService->getSimuladoById($simuladoId);
        $simuladoService->addPergunta($simulado, $validatedData);

        return redirect()->route('admin.simulados.show', $simuladoId)
            ->with('success', 'Pergunta adicionada com sucesso!');
    }
    
    /**
     * Display the videos management page
     *
     * @return \Illuminate\View\View
     */
    public function videos()
    {
        try {
            $videoService = app(\App\Services\VideoService::class);
            $videos = $videoService->getAllVideos();
            
            // Try to get courses if CourseService is available
            $courses = [];
            if (class_exists(\App\Services\CourseService::class)) {
                $courseService = app(\App\Services\CourseService::class);
                if (method_exists($courseService, 'getAllCourses')) {
                    $courses = $courseService->getAllCourses();
                }
            }
            
            return view('admin.videos.index', compact('videos', 'courses'));
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in AdminController@videos', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return an empty array for courses if there's an error
            $videos = $videos ?? collect([]);
            $courses = $courses ?? [];
            
            return view('admin.videos.index', compact('videos', 'courses'))
                ->with('error', 'Ocorreu um erro ao carregar os vídeos. Por favor, tente novamente.');
        }
    }
    
    /**
     * Display the course assignments page
     *
     * @return \Illuminate\View\View
     */
    public function atribuicoes()
    {
        $courses = app(\App\Services\CourseService::class)->getAllCourses();
        $users = $this->userService->getAllUsers();
        
        return view('admin.atribuicoes', compact('courses', 'users'));
    }
    
    /**
     * Display the gamification settings page
     *
     * @return \Illuminate\View\View
     */
    public function gamificacao()
    {
        $gamificationService = app(\App\Services\GamificationService::class);
        $settings = $gamificationService->getSettings();
        $leaderboard = $gamificationService->getLeaderboard();
        
        return view('admin.gamificacao', compact('settings', 'leaderboard'));
    }
    
    /**
     * Display the analytics dashboard
     *
     * @return \Illuminate\View\View
     */
    public function analytics()
    {
        $analyticsService = app(\App\Services\AnalyticsService::class);
        $metrics = [
            'total_users' => $this->userService->getTotalUsersCount(),
            'active_users' => $this->userService->getActiveUsersCount(),
            'course_completion' => $analyticsService->getCourseCompletionStats(),
            'engagement_metrics' => $analyticsService->getEngagementMetrics()
        ];
        
        return view('admin.analytics', compact('metrics'));
    }
    
    /**
     * Display the CMS management page
     *
     * @return \Illuminate\View\View
     */
    public function cms()
    {
        $pages = app(\App\Services\CmsService::class)->getAllPages();
        return view('admin.cms.index', compact('pages'));
    }
    
    /**
     * Display the notifications management page
     *
     * @return \Illuminate\View\View
     */
    public function notificacoes()
    {
        $notifications = app(\App\Services\NotificationService::class)->getRecentNotifications();
        $notificationTemplates = app(\App\Services\NotificationService::class)->getTemplates();
        
        return view('admin.notificacoes', compact('notifications', 'notificationTemplates'));
    }
    
    /**
     * Display the reports page
     *
     * @return \Illuminate\View\View
     */
    public function relatorios()
    {
        $reportService = app(\App\Services\ReportService::class);
        $reports = $reportService->getAvailableReports();
        
        return view('admin.relatorios', compact('reports'));
    }
    
    /**
     * Display the certificates management page
     *
     * @return \Illuminate\View\View
     */
    public function certificados()
    {
        $certificateService = app(\App\Services\CertificateService::class);
        $certificates = $certificateService->getAllCertificates();
        $templates = $certificateService->getTemplates();
        
        return view('admin.certificados', compact('certificates', 'templates'));
    }
    
    /**
     * Display the system settings page
     *
     * @return \Illuminate\View\View
     */
    public function configuracoes()
    {
        $settings = [
            'system' => config('app'),
            'mail' => config('mail'),
            'services' => config('services')
        ];
        
        return view('admin.configuracoes', compact('settings'));
    }
    
    /**
     * Display the support page
     *
     * @return \Illuminate\View\View
     */
    public function suporte()
    {
        $tickets = app(\App\Services\SupportService::class)->getRecentTickets();
        $faqs = app(\App\Services\SupportService::class)->getFaqs();
        
        return view('admin.suporte', compact('tickets', 'faqs'));
    }
}
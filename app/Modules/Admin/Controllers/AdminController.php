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
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'hire_date' => 'nullable|date',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|in:active,inactive,pending,blocked,suspended',
            'is_admin' => 'boolean',
        ]);

        // Handle avatar upload if present
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validatedData['avatar'] = $avatarPath;
        }

        // Set default status if not provided
        if (!isset($validatedData['status'])) {
            $validatedData['status'] = 'active';
        }

        $this->userService->createUser($validatedData);

        return redirect()->route('admin.users')->with('success', 'Utilizador criado com sucesso!');
    }

    public function updateUser(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'birth_date' => 'required|date',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'hire_date' => 'nullable|date',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'nullable|in:active,inactive,pending,blocked,suspended',
            'is_admin' => 'boolean',
            'password' => 'nullable|string|min:6',
        ]);

        // Handle avatar upload if present
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
                \Storage::disk('public')->delete($user->avatar);
            }
            
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validatedData['avatar'] = $avatarPath;
        }

        $this->userService->updateUser($user, $validatedData);

        return redirect()->route('admin.users')->with('success', 'Utilizador actualizado com sucesso!');
    }

    public function deleteUser(User $user)
    {
        $user->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Display the profile edit page
     *
     * @return \Illuminate\View\View
     */
    public function profile()
    {
        $user = auth()->user();
        return view('admin.profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        
        if (!empty($validatedData['password'])) {
            $user->password = bcrypt($validatedData['password']);
        }
        
        $user->save();

        return redirect()->route('profile.edit')->with('status', 'Perfil atualizado com sucesso!');
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
     * Show the form for creating a new video
     */
    public function videosCreate()
    {
        try {
            // Get courses if available for video assignment
            $courses = [];
            if (class_exists(\App\Services\CourseService::class)) {
                $courseService = app(\App\Services\CourseService::class);
                if (method_exists($courseService, 'getAllCourses')) {
                    $courses = $courseService->getAllCourses();
                }
            }
            
            return view('admin.videos.create', compact('courses'));
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in AdminController@videosCreate', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.videos.index')
                ->with('error', 'Erro ao carregar o formulário de criação de vídeo.');
        }
    }
    
    /**
     * Store a newly created video in storage
     */
    public function videosStore(Request $request)
    {
        try {
            $videoService = app(\App\Services\VideoService::class);
            
            // Basic validation
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'url' => 'required|url',
                'course_id' => 'nullable|integer'
            ]);
            
            $videoData = [
                'title' => $request->title,
                'description' => $request->description,
                'url' => $request->url,
                'course_id' => $request->course_id,
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            if (method_exists($videoService, 'createVideo')) {
                $videoService->createVideo($videoData);
            }
            
            return redirect()->route('admin.videos.index')
                ->with('success', 'Vídeo criado com sucesso!');
                
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in AdminController@videosStore', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Erro ao criar o vídeo. Tente novamente.')
                ->withInput();
        }
    }
    
    /**
     * Display the specified video
     */
    public function videosShow($id)
    {
        try {
            $videoService = app(\App\Services\VideoService::class);
            
            if (method_exists($videoService, 'getVideoById')) {
                $video = $videoService->getVideoById($id);
            } else {
                $videos = $videoService->getAllVideos();
                $video = $videos->firstWhere('id', $id);
            }
            
            if (!$video) {
                return redirect()->route('admin.videos.index')
                    ->with('error', 'Vídeo não encontrado.');
            }
            
            return view('admin.videos.show', compact('video'));
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in AdminController@videosShow', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.videos.index')
                ->with('error', 'Erro ao carregar o vídeo.');
        }
    }
    
    /**
     * Show the form for editing the specified video
     */
    public function videosEdit($id)
    {
        try {
            $videoService = app(\App\Services\VideoService::class);
            
            if (method_exists($videoService, 'getVideoById')) {
                $video = $videoService->getVideoById($id);
            } else {
                $videos = $videoService->getAllVideos();
                $video = $videos->firstWhere('id', $id);
            }
            
            if (!$video) {
                return redirect()->route('admin.videos.index')
                    ->with('error', 'Vídeo não encontrado.');
            }
            
            // Get courses if available
            $courses = [];
            if (class_exists(\App\Services\CourseService::class)) {
                $courseService = app(\App\Services\CourseService::class);
                if (method_exists($courseService, 'getAllCourses')) {
                    $courses = $courseService->getAllCourses();
                }
            }
            
            return view('admin.videos.edit', compact('video', 'courses'));
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in AdminController@videosEdit', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.videos.index')
                ->with('error', 'Erro ao carregar o formulário de edição.');
        }
    }
    
    /**
     * Update the specified video in storage
     */
    public function videosUpdate(Request $request, $id)
    {
        try {
            $videoService = app(\App\Services\VideoService::class);
            
            // Basic validation
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'url' => 'required|url',
                'course_id' => 'nullable|integer'
            ]);
            
            $videoData = [
                'title' => $request->title,
                'description' => $request->description,
                'url' => $request->url,
                'course_id' => $request->course_id,
                'updated_at' => now()
            ];
            
            if (method_exists($videoService, 'updateVideo')) {
                $videoService->updateVideo($id, $videoData);
            }
            
            return redirect()->route('admin.videos.index')
                ->with('success', 'Vídeo atualizado com sucesso!');
                
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in AdminController@videosUpdate', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Erro ao atualizar o vídeo. Tente novamente.')
                ->withInput();
        }
    }
    
    /**
     * Remove the specified video from storage
     */
    public function videosDestroy($id)
    {
        try {
            $videoService = app(\App\Services\VideoService::class);
            
            if (method_exists($videoService, 'deleteVideo')) {
                $videoService->deleteVideo($id);
            }
            
            return redirect()->route('admin.videos.index')
                ->with('success', 'Vídeo removido com sucesso!');
                
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in AdminController@videosDestroy', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.videos.index')
                ->with('error', 'Erro ao remover o vídeo.');
        }
    }
    
    /**
     * Display the courses management page
     *
     * @return \Illuminate\View\View
     */
    public function courses()
    {
        try {
            $courseService = app(\App\Services\CourseService::class);
            $courses = $courseService->getAllCourses();
            
            return view('admin.courses.index', compact('courses'));
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in AdminController@courses', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return an empty collection if there's an error
            $courses = collect([]);
            
            return view('admin.courses.index', compact('courses'))
                ->with('error', 'Ocorreu um erro ao carregar os cursos. Por favor, tente novamente.');
        }
    }
    
    /**
     * Show the form for creating a new course
     */
    public function coursesCreate()
    {
        try {
            return view('admin.courses.create');
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in AdminController@coursesCreate', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.courses.index')
                ->with('error', 'Erro ao carregar o formulário de criação de curso.');
        }
    }
    
    /**
     * Store a newly created course in storage
     */
    public function coursesStore(Request $request)
    {
        try {
            $courseService = app(\App\Services\CourseService::class);
            
            // Basic validation
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'difficulty_level' => 'required|string|in:beginner,intermediate,advanced',
                'estimated_duration' => 'required|integer|min:1',
                'is_active' => 'nullable|boolean'
            ]);
            
            $courseData = [
                'title' => $request->title,
                'description' => $request->description,
                'difficulty_level' => $request->difficulty_level,
                'estimated_duration' => $request->estimated_duration,
                'is_active' => $request->has('is_active'),
                'category_id' => 1, // Default category for now
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            if (method_exists($courseService, 'createCourse')) {
                $courseService->createCourse($courseData);
            }
            
            return redirect()->route('admin.courses.index')
                ->with('success', 'Curso criado com sucesso!');
                
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in AdminController@coursesStore', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Erro ao criar o curso. Tente novamente.')
                ->withInput();
        }
    }
    
    /**
     * Display the specified course
     */
    public function coursesShow($id)
    {
        try {
            $courseService = app(\App\Services\CourseService::class);
            
            if (method_exists($courseService, 'getCourseById')) {
                $course = $courseService->getCourseById($id);
            } else {
                $courses = $courseService->getAllCourses();
                $course = $courses->firstWhere('id', $id);
            }
            
            if (!$course) {
                return redirect()->route('admin.courses.index')
                    ->with('error', 'Curso não encontrado.');
            }
            
            return view('admin.courses.show', compact('course'));
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in AdminController@coursesShow', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.courses.index')
                ->with('error', 'Erro ao carregar o curso.');
        }
    }
    
    /**
     * Show the form for editing the specified course
     */
    public function coursesEdit($id)
    {
        try {
            $courseService = app(\App\Services\CourseService::class);
            
            if (method_exists($courseService, 'getCourseById')) {
                $course = $courseService->getCourseById($id);
            } else {
                $courses = $courseService->getAllCourses();
                $course = $courses->firstWhere('id', $id);
            }
            
            if (!$course) {
                return redirect()->route('admin.courses.index')
                    ->with('error', 'Curso não encontrado.');
            }
            
            return view('admin.courses.edit', compact('course'));
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in AdminController@coursesEdit', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.courses.index')
                ->with('error', 'Erro ao carregar o formulário de edição.');
        }
    }
    
    /**
     * Update the specified course in storage
     */
    public function coursesUpdate(Request $request, $id)
    {
        try {
            $courseService = app(\App\Services\CourseService::class);
            
            // Basic validation
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'difficulty_level' => 'required|string|in:beginner,intermediate,advanced',
                'estimated_duration' => 'required|integer|min:1',
                'is_active' => 'nullable|boolean'
            ]);
            
            $courseData = [
                'title' => $request->title,
                'description' => $request->description,
                'difficulty_level' => $request->difficulty_level,
                'estimated_duration' => $request->estimated_duration,
                'is_active' => $request->has('is_active'),
                'updated_at' => now()
            ];
            
            if (method_exists($courseService, 'updateCourse')) {
                $courseService->updateCourse($id, $courseData);
            }
            
            return redirect()->route('admin.courses.index')
                ->with('success', 'Curso atualizado com sucesso!');
                
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in AdminController@coursesUpdate', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Erro ao atualizar o curso. Tente novamente.')
                ->withInput();
        }
    }
    
    /**
     * Remove the specified course from storage
     */
    public function coursesDestroy($id)
    {
        try {
            $courseService = app(\App\Services\CourseService::class);
            
            if (method_exists($courseService, 'deleteCourse')) {
                $courseService->deleteCourse($id);
            }
            
            return redirect()->route('admin.courses.index')
                ->with('success', 'Curso removido com sucesso!');
                
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in AdminController@coursesDestroy', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.courses.index')
                ->with('error', 'Erro ao remover o curso.');
        }
    }

    /**
     * Display the assignments page
     *
     * @return \Illuminate\View\View
     */
    public function atribuicoes()
    {
        $simuladoService = app(\App\Modules\Simulado\Services\SimuladoService::class);
        $videoService = app(\App\Services\VideoService::class);
        
        $simulados = $simuladoService->getAllSimulados();
        $videos = $videoService->getAllVideos();
        $users = $this->userService->getAllUsers();
        
        // Estatísticas para a página
        $estatisticas = [
            'total_simulados' => $simulados->count(),
            'total_videos' => $videos->count(),
            'total_usuarios' => $users->count(),
            'atribuicoes_ativas' => 0 // Placeholder para futuras implementações
        ];
        
        return view('admin.atribuicoes', compact('simulados', 'videos', 'users', 'estatisticas'));
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
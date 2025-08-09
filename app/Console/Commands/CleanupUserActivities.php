<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\UserActivityService;
use App\Services\ActivityAlertService;

class CleanupUserActivities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activities:cleanup 
                            {--days=90 : Number of days to keep activities}
                            {--process-alerts : Process security alerts before cleanup}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old user activities and optionally process security alerts';

    protected UserActivityService $activityService;
    protected ActivityAlertService $alertService;

    public function __construct(UserActivityService $activityService, ActivityAlertService $alertService)
    {
        parent::__construct();
        $this->activityService = $activityService;
        $this->alertService = $alertService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $processAlerts = $this->option('process-alerts');
        $dryRun = $this->option('dry-run');

        $this->info("🧹 Iniciando limpeza de atividades de usuários...");
        $this->info("📅 Mantendo atividades dos últimos {$days} dias");

        if ($dryRun) {
            $this->warn("🔍 Modo de teste ativo - nenhum dado será removido");
        }

        // Process security alerts first if requested
        if ($processAlerts) {
            $this->info("🚨 Processando alertas de segurança...");
            try {
                $alerts = $this->alertService->processAlerts();
                $this->info("✅ Processados " . count($alerts) . " alertas de segurança");
                
                if (count($alerts) > 0) {
                    $this->table(
                        ['Tipo', 'Severidade', 'Mensagem'],
                        collect($alerts)->map(function ($alert) {
                            return [
                                $alert['type'],
                                $alert['severity'],
                                substr($alert['message'], 0, 50) . '...'
                            ];
                        })->toArray()
                    );
                }
            } catch (\Exception $e) {
                $this->error("❌ Erro ao processar alertas: " . $e->getMessage());
                return self::FAILURE;
            }
        }

        // Get statistics before cleanup
        $this->info("📊 Obtendo estatísticas antes da limpeza...");
        $beforeStats = $this->activityService->getSystemActivityStats();
        $this->info("📈 Total de atividades antes: " . number_format($beforeStats['total_activities']));

        // Perform cleanup
        try {
            if ($dryRun) {
                // Simulate cleanup to show what would be deleted
                $oldActivitiesCount = $this->activityService->getOldActivitiesCount($days);
                $this->info("🗑️  Seriam removidas {$oldActivitiesCount} atividades antigas");
                
                // Show breakdown by activity type
                $breakdown = $this->activityService->getOldActivitiesBreakdown($days);
                if (!empty($breakdown)) {
                    $this->table(
                        ['Tipo de Atividade', 'Quantidade'],
                        collect($breakdown)->map(function ($count, $type) {
                            return [$type, number_format($count)];
                        })->toArray()
                    );
                }
            } else {
                $deletedCount = $this->activityService->cleanOldActivities($days);
                $this->info("✅ Removidas {$deletedCount} atividades antigas com sucesso");
            }
        } catch (\Exception $e) {
            $this->error("❌ Erro durante a limpeza: " . $e->getMessage());
            return self::FAILURE;
        }

        // Get statistics after cleanup (if not dry run)
        if (!$dryRun) {
            $afterStats = $this->activityService->getSystemActivityStats();
            $this->info("📉 Total de atividades após: " . number_format($afterStats['total_activities']));
            
            $saved = $beforeStats['total_activities'] - $afterStats['total_activities'];
            $this->info("💾 Espaço liberado: {$saved} registros removidos");
        }

        // Show recent activity summary
        $this->info("📋 Resumo de atividades recentes:");
        $recentStats = $this->activityService->getRecentActivitySummary(7);
        
        $this->table(
            ['Período', 'Logins', 'Page Views', 'Form Submits', 'Total'],
            [
                [
                    'Últimos 7 dias',
                    number_format($recentStats['logins'] ?? 0),
                    number_format($recentStats['page_views'] ?? 0),
                    number_format($recentStats['form_submits'] ?? 0),
                    number_format($recentStats['total'] ?? 0)
                ]
            ]
        );

        // Show inactive users warning
        $inactiveUsers = $this->activityService->getInactiveUsers(30);
        if (count($inactiveUsers) > 0) {
            $this->warn("⚠️  Encontrados " . count($inactiveUsers) . " usuários inativos há mais de 30 dias:");
            
            $this->table(
                ['Nome', 'Email', 'Último Login', 'Dias Inativo'],
                collect($inactiveUsers)->take(10)->map(function ($user) {
                    return [
                        $user['name'],
                        $user['email'],
                        $user['last_login'] ? $user['last_login']->format('d/m/Y H:i') : 'Nunca',
                        $user['days_inactive']
                    ];
                })->toArray()
            );
            
            if (count($inactiveUsers) > 10) {
                $this->info("... e mais " . (count($inactiveUsers) - 10) . " usuários");
            }
        }

        $this->info("🎉 Limpeza de atividades concluída com sucesso!");
        
        return self::SUCCESS;
    }
}

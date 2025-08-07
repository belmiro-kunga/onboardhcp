<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Models\Video;
use App\Models\VideoView;
use App\Models\Enrollment;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Barryvdh\DomPDF\Facade\Pdf;

class AnalyticsController extends Controller
{
    /**
     * The analytics service instance.
     *
     * @var \App\Services\AnalyticsService
     */
    protected $analyticsService;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\AnalyticsService  $analyticsService
     * @return void
     */
    public function __construct(AnalyticsService $analyticsService)
    {
        $this->middleware('auth:api');
        $this->analyticsService = $analyticsService;
    }

    /**
     * Get analytics for a specific course.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCourseAnalytics(Request $request, Course $course): JsonResponse
    {
        $this->authorize('viewAnalytics', $course);

        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        
        $analytics = $this->analyticsService->getCourseAnalytics(
            $course,
            $startDate,
            $endDate
        );

        return response()->json([
            'data' => $analytics,
            'meta' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'timezone' => config('app.timezone'),
            ]
        ]);
    }

    /**
     * Get progress for a specific user in a course.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Course  $course
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserProgress(Request $request, Course $course, User $user): JsonResponse
    {
        $this->authorize('viewProgress', [$course, $user]);

        $progress = $this->analyticsService->getUserProgress($user, $course);

        return response()->json([
            'data' => $progress,
            'meta' => [
                'user_id' => $user->id,
                'course_id' => $course->id,
                'last_updated' => now()->toIso8601String(),
            ]
        ]);
    }

    /**
     * Export progress report for a course.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Course  $course
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\Response
     */
    public function exportProgressReport(Request $request, Course $course)
    {
        $this->authorize('exportProgress', $course);

        $format = $request->input('format', 'csv');
        $includeInactive = $request->boolean('include_inactive', false);
        $statusFilter = $request->input('status');
        
        $data = $this->analyticsService->prepareProgressReportData($course, $includeInactive, $statusFilter);

        if ($format === 'pdf') {
            $pdf = PDF::loadView('exports.progress-report', [
                'course' => $course,
                'enrollments' => $data,
                'generatedAt' => now()->format('Y-m-d H:i:s'),
            ]);

            return $pdf->download("progress-report-{$course->slug}.pdf");
        }

        // Default to CSV
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=progress-report-{$course->slug}.csv",
        ];

        return response()->stream(function () use ($data, $course) {
            $handle = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($handle, [
                'Student ID',
                'Name',
                'Email',
                'Enrollment Date',
                'Completion %',
                'Videos Watched',
                'Total Videos',
                'Last Activity',
                'Status',
            ]);

            // Add data rows
            foreach ($data as $enrollment) {
                fputcsv($handle, [
                    $enrollment->user->id,
                    $enrollment->user->name,
                    $enrollment->user->email,
                    $enrollment->created_at->format('Y-m-d'),
                    $enrollment->completion_percentage,
                    $enrollment->videos_watched_count,
                    $enrollment->course->videos_count,
                    $enrollment->last_activity_at?->format('Y-m-d H:i:s') ?? 'Never',
                    $enrollment->status,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Get dashboard statistics for admin overview.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboardStats(Request $request): JsonResponse
    {
        $this->authorize('viewDashboardStats', User::class);

        $timeRange = $request->input('time_range', '30d');
        $now = now();
        
        // Parse time range
        switch ($timeRange) {
            case '7d':
                $startDate = $now->copy()->subDays(7);
                break;
            case '90d':
                $startDate = $now->copy()->subDays(90);
                break;
            case '1y':
                $startDate = $now->copy()->subYear();
                break;
            case 'all':
                $startDate = null;
                break;
            case '30d':
            default:
                $startDate = $now->copy()->subDays(30);
                break;
        }

        $stats = $this->analyticsService->getDashboardStats($startDate);

        return response()->json([
            'data' => $stats,
            'meta' => [
                'time_range' => $timeRange,
                'start_date' => $startDate?->toDateString(),
                'end_date' => $now->toDateString(),
                'timezone' => config('app.timezone'),
            ]
        ]);
    }

    /**
     * Get real-time updates for the dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRealtimeUpdates(Request $request): JsonResponse
    {
        $this->authorize('viewRealtimeUpdates', User::class);

        $channels = $request->input('channels', ['enrollments', 'completions']);
        $lastUpdate = $request->input('last_update');
        
        $updates = $this->analyticsService->getRealtimeUpdates($channels, $lastUpdate);

        return response()->json([
            'data' => $updates,
            'meta' => [
                'timestamp' => now()->toIso8601String(),
                'channels' => $channels,
            ]
        ]);
    }
}

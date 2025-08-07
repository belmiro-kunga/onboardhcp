<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCourseRequest;
use App\Http\Requests\Admin\UpdateCourseRequest;
use App\Http\Requests\Admin\StoreVideoRequest;
use App\Http\Requests\Admin\UpdateVideoRequest;
use App\Http\Requests\Admin\BulkUploadRequest;
use App\Models\Course;
use App\Models\Category;
use App\Models\Video;
use App\Models\User;
use App\Services\CourseService;
use App\Services\VideoProcessingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Bus;
use App\Jobs\ProcessVideoUpload;
use App\Jobs\ProcessBulkVideoUploads;
use Inertia\Inertia;

class AdminVideoController extends Controller
{
    /**
     * The course service instance.
     *
     * @var \App\Services\CourseService
     */
    protected $courseService;
    protected $videoService;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\CourseService  $courseService
     * @param  \App\Services\VideoProcessingService  $videoService
     * @return void
     */
    public function __construct(CourseService $courseService, VideoProcessingService $videoService)
    {
        $this->middleware('auth');
        $this->middleware('can:manage-courses');
        $this->courseService = $courseService;
        $this->videoService = $videoService;
    }

    /**
     * Display a listing of the courses with pagination, filtering, and search.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Course::class);

        $filters = [
            'search' => $request->query('search', ''),
            'status' => $request->query('status', ''),
            'category' => $request->query('category', ''),
            'level' => $request->query('level', ''),
            'sort' => $request->query('sort', 'newest'),
        ];

        $courses = $this->courseService->getFilteredCourses($filters);

        $categories = Category::select('id', 'name')->get();

        return Inertia::render('Admin/Courses/Index', [
            'courses' => $courses->paginate(15)->withQueryString(),
            'filters' => $filters,
            'categories' => $categories,
            'levels' => [
                'beginner' => 'Beginner',
                'intermediate' => 'Intermediate',
                'advanced' => 'Advanced',
            ],
        ]);
    }

    /**
     * Show the form for creating a new course.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $this->authorize('create', Course::class);

        return Inertia::render('Admin/Courses/Create', [
            'categories' => Category::select('id', 'name')->get(),
            'levels' => [
                'beginner' => 'Beginner',
                'intermediate' => 'Intermediate',
                'advanced' => 'Advanced',
            ],
        ]);
    }

    /**
     * Store a newly created course in storage.
     *
     * @param  \App\Http\Requests\Admin\StoreCourseRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreCourseRequest $request)
    {
        $data = $request->validated();

        // Handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail_path'] = $this->uploadThumbnail($request->file('thumbnail'));
        }

        // Create the course
        $course = $this->courseService->createCourse(
            array_merge($data, [
                'instructor_id' => $request->user()->id,
            ])
        );

        return redirect()
            ->route('admin.courses.show', $course->id)
            ->with('success', 'Course created successfully.');
    }

    /**
     * Display the specified course with its videos.
     *
     * @param  \App\Models\Course  $course
     * @return \Inertia\Response
     */
    public function show(Course $course)
    {
        $this->authorize('view', $course);

        $course->load([
            'category:id,name',
            'instructor:id,name,email',
            'videos' => function ($query) {
                $query->orderBy('position');
            },
        ]);

        return Inertia::render('Admin/Courses/Show', [
            'course' => $course,
            'videos' => $course->videos,
            'can' => [
                'update' => auth()->user()->can('update', $course),
                'delete' => auth()->user()->can('delete', $course),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified course.
     *
     * @param  \App\Models\Course  $course
     * @return \Inertia\Response
     */
    public function edit(Course $course)
    {
        $this->authorize('update', $course);

        $course->load('category:id,name');

        return Inertia::render('Admin/Courses/Edit', [
            'course' => $course,
            'categories' => Category::select('id', 'name')->get(),
            'levels' => [
                'beginner' => 'Beginner',
                'intermediate' => 'Intermediate',
                'advanced' => 'Advanced',
            ],
        ]);
    }

    /**
     * Update the specified course in storage.
     *
     * @param  \App\Http\Requests\Admin\UpdateCourseRequest  $request
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateCourseRequest $request, Course $course)
    {
        $data = $request->validated();

        // Handle thumbnail upload if a new one is provided
        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail if it exists
            if ($course->thumbnail_path) {
                Storage::delete($course->thumbnail_path);
            }
            $data['thumbnail_path'] = $this->uploadThumbnail($request->file('thumbnail'));
        }

        // Update the course
        $this->courseService->updateCourse($course, $data);

        return redirect()
            ->route('admin.courses.show', $course->id)
            ->with('success', 'Course updated successfully.');
    }

    /**
     * Remove the specified course from storage.
     *
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Course $course)
    {
        $this->authorize('delete', $course);

        // Use a transaction to ensure data consistency
        return DB::transaction(function () use ($course) {
            // Soft delete the course (this will cascade to related models with onDelete('cascade'))
            $course->delete();

            return redirect()
                ->route('admin.courses.index')
                ->with('success', 'Course deleted successfully.');
        });
    }

    /**
     * Upload course thumbnail and return the path.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return string
     */
    protected function uploadThumbnail($file)
    {
        $path = $file->store('public/course_thumbnails');
        return str_replace('public/', '', $path);
    }

    /**
     * Reorder course videos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Store a new video for a course.
     *
     * @param  \App\Http\Requests\Admin\StoreVideoRequest  $request
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeVideo(StoreVideoRequest $request, Course $course)
    {
        $this->authorize('update', $course);

        return DB::transaction(function () use ($request, $course) {
            $data = $request->validated();
            $data['course_id'] = $course->id;
            $data['user_id'] = $request->user()->id;
            $data['slug'] = Str::slug($data['title']) . '-' . time();

            // Handle thumbnail upload
            if ($request->hasFile('thumbnail')) {
                $data['thumbnail_path'] = $this->uploadThumbnail($request->file('thumbnail'));
            }

            // Process video based on source type
            $video = $this->processVideoSource($request, $data);

            // Create the video record
            $video = Video::create($video);

            // Dispatch job to process video if needed (e.g., for uploads)
            if ($request->source_type === 'upload') {
                ProcessVideoUpload::dispatch($video)->onQueue('video-processing');
            }

            return response()->json([
                'message' => 'Video created successfully',
                'video' => $video->load('course')
            ], 201);
        });
    }

    /**
     * Update an existing video.
     *
     * @param  \App\Http\Requests\Admin\UpdateVideoRequest  $request
     * @param  \App\Models\Video  $video
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateVideo(UpdateVideoRequest $request, Video $video)
    {
        $this->authorize('update', $video->course);

        return DB::transaction(function () use ($request, $video) {
            $data = $request->validated();
            $data['slug'] = Str::slug($data['title']) . '-' . $video->id;

            // Handle thumbnail upload
            if ($request->hasFile('thumbnail')) {
                // Delete old thumbnail if it exists
                if ($video->thumbnail_path) {
                    Storage::delete($video->thumbnail_path);
                }
                $data['thumbnail_path'] = $this->uploadThumbnail($request->file('thumbnail'));
            }

            // If source type is changing, handle the transition
            if ($request->has('source_type') && $request->source_type !== $video->source_type) {
                $data = array_merge($data, $this->handleSourceTypeChange($request, $video));
            }

            // Update the video
            $video->update($data);

            // Refresh metadata if requested
            if ($request->boolean('refresh_metadata')) {
                $this->videoService->refreshVideoMetadata($video);
            }

            return response()->json([
                'message' => 'Video updated successfully',
                'video' => $video->fresh()
            ]);
        });
    }

    /**
     * Delete a video and its associated files.
     *
     * @param  \App\Models\Video  $video
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyVideo(Video $video)
    {
        $this->authorize('delete', $video->course);

        return DB::transaction(function () use ($video) {
            // Delete associated files
            if ($video->source_type === 'upload' && $video->file_path) {
                Storage::delete($video->file_path);
            }
            
            if ($video->thumbnail_path) {
                Storage::delete($video->thumbnail_path);
            }

            // Delete any other related files (e.g., subtitles, previews)
            $this->videoService->cleanupVideoFiles($video);

            // Delete the video record
            $video->delete();

            return response()->json(['message' => 'Video deleted successfully']);
        });
    }

    /**
     * Reorder videos within a course.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Course  $course
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorderVideos(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $request->validate([
            'videos' => 'required|array',
            'videos.*.id' => 'required|exists:videos,id',
            'videos.*.position' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request, $course) {
            foreach ($request->videos as $videoData) {
                Video::where('id', $videoData['id'])
                    ->where('course_id', $course->id)
                    ->update(['position' => $videoData['position']]);
            }
        });

        return response()->json(['message' => 'Videos reordered successfully']);
    }

    /**
     * Handle bulk video uploads.
     *
     * @param  \App\Http\Requests\Admin\BulkUploadRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkUpload(BulkUploadRequest $request)
    {
        $course = Course::findOrFail($request->course_id);
        $this->authorize('update', $course);

        $batch = Bus::batch([])->dispatch();
        $uploads = [];

        foreach ($request->videos as $videoData) {
            $upload = [
                'file' => $videoData['file']->store('temp-uploads'),
                'title' => $videoData['title'],
                'description' => $videoData['description'] ?? null,
                'is_free' => $videoData['is_free'] ?? false,
                'is_published' => $videoData['is_published'] ?? false,
                'position' => $videoData['position'] ?? null,
                'metadata' => $videoData['metadata'] ?? [],
                'course_id' => $course->id,
                'user_id' => $request->user()->id,
            ];

            $uploads[] = $upload;
        }

        // Dispatch the bulk processing job
        ProcessBulkVideoUploads::dispatch(
            $uploads,
            $request->user(),
            $request->boolean('notify_on_complete') ? $request->email_notification : null
        )->onQueue('video-processing');

        return response()->json([
            'message' => 'Videos are being processed. You will be notified when complete.',
            'batch_id' => $batch->id,
        ], 202);
    }

    /**
     * Process video source based on type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $data
     * @return array
     */
    protected function processVideoSource($request, array $data): array
    {
        switch ($data['source_type']) {
            case 'upload':
                $file = $request->file('video_file');
                $path = $file->store('videos/' . now()->format('Y/m'));
                $data['file_path'] = $path;
                $data['file_size'] = $file->getSize();
                $data['mime_type'] = $file->getMimeType();
                break;
                
            case 'youtube':
            case 'vimeo':
                $data['external_id'] = $this->videoService->extractVideoId($data['source_url'], $data['source_type']);
                $data['embed_url'] = $this->videoService->generateEmbedUrl($data['source_url'], $data['source_type']);
                break;
                
            case 's3':
                $data['file_path'] = $data['s3_path'];
                break;
                
            case 'bunny':
                $data['external_id'] = $data['bunny_video_id'];
                break;
        }

        return $data;
    }

    /**
     * Handle source type change for a video.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Video  $video
     * @return array
     */
    protected function handleSourceTypeChange($request, Video $video): array
    {
        $data = [];
        
        // Clean up old source files if needed
        if ($video->source_type === 'upload' && $video->file_path) {
            Storage::delete($video->file_path);
            $data['file_path'] = null;
            $data['file_size'] = null;
            $data['mime_type'] = null;
        }
        
        // Reset external IDs and URLs
        $data['external_id'] = null;
        $data['embed_url'] = null;
        
        // Process new source type
        if ($request->source_type === 'upload' && $request->hasFile('video_file')) {
            $file = $request->file('video_file');
            $path = $file->store('videos/' . now()->format('Y/m'));
            $data['file_path'] = $path;
            $data['file_size'] = $file->getSize();
            $data['mime_type'] = $file->getMimeType();
        } elseif (in_array($request->source_type, ['youtube', 'vimeo']) && $request->filled('source_url')) {
            $data['external_id'] = $this->videoService->extractVideoId($request->source_url, $request->source_type);
            $data['embed_url'] = $this->videoService->generateEmbedUrl($request->source_url, $request->source_type);
        } elseif ($request->source_type === 's3' && $request->filled('s3_path')) {
            $data['file_path'] = $request->s3_path;
        } elseif ($request->source_type === 'bunny' && $request->filled('bunny_video_id')) {
            $data['external_id'] = $request->bunny_video_id;
        }
        
        return $data;
    }
}

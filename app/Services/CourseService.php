<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Category;
use App\Models\Video;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class CourseService
{
    /**
     * Get all courses with optional filters
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllCourses(array $filters = [])
    {
        try {
            $query = Course::with(['category' => function($query) {
                $query->select('id', 'name', 'slug');
            }])
            ->select([
                'id', 
                'title', 
                'slug', 
                'description', 
                'category_id', 
                'difficulty_level', 
                'estimated_duration', 
                'is_active', 
                'order_index', 
                'created_at'
            ]);

            // Apply filters if any
            if (!empty($filters['is_active'])) {
                $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
            }

            if (!empty($filters['category_id'])) {
                $query->where('category_id', $filters['category_id']);
            }

            if (!empty($filters['difficulty_level'])) {
                $query->where('difficulty_level', $filters['difficulty_level']);
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Order by category and then by order_index
            $courses = $query->orderBy('category_id')
                            ->orderBy('order_index')
                            ->get()
                            ->map(function($course) {
                                return [
                                    'id' => $course->id,
                                    'title' => $course->title,
                                    'slug' => $course->slug,
                                    'description' => $course->description,
                                    'difficulty_level' => $course->difficulty_level,
                                    'estimated_duration' => $this->formatDuration($course->estimated_duration * 60), // Convert minutes to seconds
                                    'is_active' => $course->is_active,
                                    'order_index' => $course->order_index,
                                    'created_at' => $course->created_at->format('d/m/Y H:i'),
                                    'category' => $course->category ? [
                                        'id' => $course->category->id,
                                        'name' => $course->category->name,
                                        'slug' => $course->category->slug
                                    ] : null,
                                    'stats' => [
                                        'total_videos' => $course->videos()->count(),
                                        'active_videos' => $course->videos()->where('is_active', true)->count()
                                    ]
                                ];
                            });

            return $courses;

        } catch (Exception $e) {
            Log::error('Error fetching all courses', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return empty collection on error
            return collect([]);
        }
    }

    /**
     * Get a course by ID with related data
     *
     * @param int $id
     * @return array|null
     */
    public function getCourseById(int $id): ?array
    {
        try {
            $course = Course::with(['category', 'videos' => function($query) {
                $query->orderBy('order_index');
            }])
            ->findOrFail($id);

            return [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'description' => $course->description,
                'category_id' => $course->category_id,
                'difficulty_level' => $course->difficulty_level,
                'estimated_duration' => $this->formatDuration($course->estimated_duration * 60),
                'is_active' => $course->is_active,
                'order_index' => $course->order_index,
                'created_at' => $course->created_at->format('d/m/Y H:i'),
                'updated_at' => $course->updated_at->format('d/m/Y H:i'),
                'category' => $course->category ? [
                    'id' => $course->category->id,
                    'name' => $course->category->name,
                    'slug' => $course->category->slug
                ] : null,
                'videos' => $course->videos->map(function($video) {
                    return [
                        'id' => $video->id,
                        'title' => $video->title,
                        'description' => $video->description,
                        'duration' => $this->formatDuration($video->duration_seconds),
                        'order_index' => $video->order_index,
                        'is_active' => $video->is_active,
                        'created_at' => $video->created_at->format('d/m/Y H:i')
                    ];
                })
            ];

        } catch (Exception $e) {
            Log::error('Error fetching course by ID', [
                'course_id' => $id,
                'message' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Create a new course
     *
     * @param array $data
     * @return array
     */
    public function createCourse(array $data): array
    {
        try {
            // Validate input data
            $validated = $this->validateCourseData($data);
            
            // Create slug from title if not provided
            if (empty($validated['slug'])) {
                $validated['slug'] = \Illuminate\Support\Str::slug($validated['title']);
            }

            // Set default order_index if not provided
            if (!isset($validated['order_index'])) {
                $validated['order_index'] = Course::where('category_id', $validated['category_id'])
                    ->max('order_index') + 1;
            }

            // Create the course
            $course = Course::create($validated);

            Log::info('Course created successfully', [
                'course_id' => $course->id,
                'title' => $course->title
            ]);

            return [
                'success' => true,
                'course' => $this->getCourseById($course->id)
            ];

        } catch (\Illuminate\Validation\ValidationException $e) {
            return [
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed'
            ];
        } catch (Exception $e) {
            Log::error('Error creating course', [
                'message' => $e->getMessage(),
                'data' => $data
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to create course: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update an existing course
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function updateCourse(int $id, array $data): array
    {
        try {
            $course = Course::findOrFail($id);
            
            // Validate input data
            $validated = $this->validateCourseData($data, $id);

            // Update the course
            $course->update($validated);

            Log::info('Course updated successfully', [
                'course_id' => $course->id,
                'updated_fields' => array_keys($validated)
            ]);

            return [
                'success' => true,
                'course' => $this->getCourseById($course->id)
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Course not found'
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            return [
                'success' => false,
                'errors' => $e->errors(),
                'message' => 'Validation failed'
            ];
        } catch (Exception $e) {
            Log::error('Error updating course', [
                'course_id' => $id,
                'message' => $e->getMessage(),
                'data' => $data
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to update course: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete a course
     *
     * @param int $id
     * @return array
     */
    public function deleteCourse(int $id): array
    {
        try {
            $course = Course::findOrFail($id);
            
            // Check if course has videos
            if ($course->videos()->exists()) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete course with associated videos. Please remove all videos first.'
                ];
            }

            // Delete the course
            $course->delete();

            Log::info('Course deleted successfully', [
                'course_id' => $id
            ]);

            return [
                'success' => true,
                'message' => 'Course deleted successfully'
            ];

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return [
                'success' => false,
                'message' => 'Course not found'
            ];
        } catch (Exception $e) {
            Log::error('Error deleting course', [
                'course_id' => $id,
                'message' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to delete course: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validate course data
     *
     * @param array $data
     * @param int|null $id
     * @return array
     */
    private function validateCourseData(array $data, ?int $id = null): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:courses,slug' . ($id ? ",$id" : ''),
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'difficulty_level' => 'required|in:beginner,intermediate,advanced',
            'estimated_duration' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'order_index' => 'nullable|integer|min:0'
        ];

        return validator($data, $rules)->validate();
    }

    /**
     * Format duration in seconds to HH:MM:SS format
     *
     * @param int $seconds
     * @return string
     */
    private function formatDuration(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}

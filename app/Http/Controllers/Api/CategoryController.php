<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Http\Requests\CategoryReorderRequest;
use App\Http\Requests\CategoryBulkActionRequest;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories with course counts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::withCount(['courses'])
            ->orderBy('order')
            ->orderBy('name');

        // Filter by search term if provided
        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Return paginated or all results
        $categories = $request->boolean('paginate', true)
            ? $query->paginate($request->input('per_page', 15))
            : $query->get();

        return response()->json([
            'data' => $categories,
            'meta' => [
                'total_categories' => Category::count(),
                'default_category_id' => config('app.default_category_id', 1),
            ],
        ]);
    }

    /**
     * Store a newly created category.
     *
     * @param  \App\Http\Requests\CategoryStoreRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CategoryStoreRequest $request): JsonResponse
    {
        $category = DB::transaction(function () use ($request) {
            // Get the highest order value
            $maxOrder = Category::max('order') ?? 0;
            
            // Create the category
            $category = Category::create([
                'name' => $request->name,
                'slug' => $this->generateUniqueSlug($request->name),
                'description' => $request->description,
                'icon' => $request->icon,
                'color' => $request->color,
                'is_active' => $request->boolean('is_active', true),
                'is_featured' => $request->boolean('is_featured', false),
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'order' => $maxOrder + 1,
            ]);

            // Handle icon upload if present
            if ($request->hasFile('icon_file')) {
                $category->addMediaFromRequest('icon_file')
                    ->toMediaCollection('category_icons');
            }

            return $category;
        });

        return response()->json([
            'message' => 'Category created successfully.',
            'data' => $category->loadCount('courses'),
        ], 201);
    }

    /**
     * Display the specified category with course count.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'data' => $category->loadCount('courses'),
        ]);
    }

    /**
     * Update the specified category.
     *
     * @param  \App\Http\Requests\CategoryUpdateRequest  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(CategoryUpdateRequest $request, Category $category): JsonResponse
    {
        $category = DB::transaction(function () use ($request, $category) {
            // Prevent updating the default category's name and slug
            if ($category->id === config('app.default_category_id')) {
                $request->merge([
                    'name' => $category->name,
                    'slug' => $category->slug,
                ]);
            }

            // Update the category
            $category->update([
                'name' => $request->name,
                'slug' => $request->has('name') 
                    ? $this->generateUniqueSlug($request->name, $category->id) 
                    : $category->slug,
                'description' => $request->description,
                'icon' => $request->icon ?? $category->icon,
                'color' => $request->color ?? $category->color,
                'is_active' => $request->has('is_active') 
                    ? $request->boolean('is_active') 
                    : $category->is_active,
                'is_featured' => $request->has('is_featured')
                    ? $request->boolean('is_featured')
                    : $category->is_featured,
                'meta_title' => $request->meta_title ?? $category->meta_title,
                'meta_description' => $request->meta_description ?? $category->meta_description,
            ]);

            // Handle icon upload if present
            if ($request->hasFile('icon_file')) {
                $category->clearMediaCollection('category_icons');
                $category->addMediaFromRequest('icon_file')
                    ->toMediaCollection('category_icons');
            }

            return $category;
        });

        return response()->json([
            'message' => 'Category updated successfully.',
            'data' => $category->loadCount('courses'),
        ]);
    }

    /**
     * Remove the specified category.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Category $category): JsonResponse
    {
        // Prevent deleting the default category
        if ($category->id === config('app.default_category_id')) {
            return response()->json([
                'message' => 'The default category cannot be deleted.',
            ], 422);
        }

        $defaultCategoryId = config('app.default_category_id', 1);
        $defaultCategory = Category::find($defaultCategoryId);

        if (!$defaultCategory) {
            return response()->json([
                'message' => 'Default category not found. Please set a valid default category ID in the configuration.',
            ], 500);
        }

        DB::transaction(function () use ($category, $defaultCategory) {
            // Reassign courses to default category
            $category->courses()->update(['category_id' => $defaultCategory->id]);
            
            // Delete the category
            $category->delete();
            
            // Reorder remaining categories
            $this->reorderCategories();
        });

        return response()->json([
            'message' => 'Category deleted successfully. All associated courses have been moved to the default category.',
        ]);
    }

    /**
     * Reorder categories.
     *
     * @param  \App\Http\Requests\CategoryReorderRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorder(CategoryReorderRequest $request): JsonResponse
    {
        $order = $request->input('order');
        
        DB::transaction(function () use ($order) {
            foreach ($order as $position => $categoryId) {
                Category::where('id', $categoryId)->update(['order' => $position + 1]);
            }
        });

        return response()->json([
            'message' => 'Categories reordered successfully.',
            'data' => Category::orderBy('order')->get(),
        ]);
    }

    /**
     * Perform bulk actions on categories.
     *
     * @param  \App\Http\Requests\CategoryBulkActionRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkAction(CategoryBulkActionRequest $request): JsonResponse
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);
        $defaultCategoryId = config('app.default_category_id', 1);
        
        // Filter out the default category from bulk actions
        $ids = array_filter($ids, function ($id) use ($defaultCategoryId) {
            return (int)$id !== (int)$defaultCategoryId;
        });

        if (empty($ids)) {
            return response()->json([
                'message' => 'No valid categories selected for bulk action.',
            ], 422);
        }

        $count = 0;
        $defaultCategory = Category::find($defaultCategoryId);
        
        DB::transaction(function () use ($action, $ids, $defaultCategory, &$count) {
            switch ($action) {
                case 'delete':
                    // Reassign courses to default category
                    Course::whereIn('category_id', $ids)
                        ->update(['category_id' => $defaultCategory->id]);
                        
                    // Delete categories
                    $count = Category::whereIn('id', $ids)->delete();
                    
                    // Reorder remaining categories
                    $this->reorderCategories();
                    break;
                    
                case 'activate':
                    $count = Category::whereIn('id', $ids)
                        ->update(['is_active' => true]);
                    break;
                    
                case 'deactivate':
                    $count = Category::whereIn('id', $ids)
                        ->update(['is_active' => false]);
                    break;
                    
                case 'feature':
                    $count = Category::whereIn('id', $ids)
                        ->update(['is_featured' => true]);
                    break;
                    
                case 'unfeature':
                    $count = Category::whereIn('id', $ids)
                        ->update(['is_featured' => false]);
                    break;
            }
        });

        return response()->json([
            'message' => "{$count} categories updated successfully.",
        ]);
    }

    /**
     * Generate a unique slug for the category.
     *
     * @param  string  $name
     * @param  int|null  $exceptId
     * @return string
     */
    protected function generateUniqueSlug(string $name, ?int $exceptId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;
        
        while (Category::where('slug', $slug)
            ->when($exceptId, function ($query) use ($exceptId) {
                $query->where('id', '!=', $exceptId);
            })
            ->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }
        
        return $slug;
    }
    
    /**
     * Reorder all categories to ensure sequential order.
     *
     * @return void
     */
    protected function reorderCategories(): void
    {
        $categories = Category::orderBy('order')->get();
        
        foreach ($categories as $index => $category) {
            $category->update(['order' => $index + 1]);
        }
    }
}

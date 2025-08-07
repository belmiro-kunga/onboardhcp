<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Course;

class UpdateCourseRequest extends StoreCourseRequest
{
    /**
     * The course instance.
     *
     * @var \App\Models\Course
     */
    protected $course;

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        
        $this->course = $this->route('course');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        
        // Make slug unique except for the current course
        $rules['slug'] = [
            'required',
            'string',
            'max:255',
            Rule::unique('courses', 'slug')->ignore($this->course->id),
        ];
        
        // Make thumbnail optional for updates
        $rules['thumbnail'] = ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'];
        
        return $rules;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only allow updates if user can update the course
        return $this->user()->can('update', $this->course);
    }
}

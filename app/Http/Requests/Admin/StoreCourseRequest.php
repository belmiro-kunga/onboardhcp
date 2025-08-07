<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Category;

class StoreCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Course::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:courses,slug'],
            'description' => ['required', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_published' => ['boolean'],
            'is_featured' => ['boolean'],
            'level' => ['required', 'string', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'thumbnail' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'prerequisites' => ['nullable', 'array'],
            'prerequisites.*' => ['string'],
            'objectives' => ['nullable', 'array'],
            'objectives.*' => ['string'],
            'target_audience' => ['nullable', 'array'],
            'target_audience.*' => ['string'],
            'required_skill' => ['nullable', 'string', 'max:255'],
            'required_skill_level' => ['nullable', 'integer', 'min:1'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('prerequisites') && is_string($this->prerequisites)) {
            $this->merge([
                'prerequisites' => json_decode($this->prerequisites, true) ?? []
            ]);
        }

        if ($this->has('objectives') && is_string($this->objectives)) {
            $this->merge([
                'objectives' => json_decode($this->objectives, true) ?? []
            ]);
        }

        if ($this->has('target_audience') && is_string($this->target_audience)) {
            $this->merge([
                'target_audience' => json_decode($this->target_audience, true) ?? []
            ]);
        }
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'category',
            'is_published' => 'published status',
            'is_featured' => 'featured status',
            'start_date' => 'start date',
            'end_date' => 'end date',
        ];
    }
}

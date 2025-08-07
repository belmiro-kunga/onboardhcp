<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class CategoryStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories')->whereNull('deleted_at'),
            ],
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:50',
            'color' => [
                'nullable',
                'string',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'
            ],
            'is_active' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
            'meta_title' => 'nullable|string|max:100',
            'meta_description' => 'nullable|string|max:200',
            'icon_file' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:2048', // 2MB
                'dimensions:max_width=512,max_height=512,ratio=1/1',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'The category name is required.',
            'name.unique' => 'A category with this name already exists.',
            'color.regex' => 'The color must be a valid hex color code (e.g., #ffffff).',
            'icon_file.image' => 'The icon must be an image file.',
            'icon_file.mimes' => 'The icon must be a file of type: jpeg, png, jpg, gif, or svg.',
            'icon_file.max' => 'The icon may not be greater than 2MB.',
            'icon_file.dimensions' => 'The icon must be square with a maximum size of 512x512 pixels.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        if ($this->has('name')) {
            $this->merge([
                'name' => trim($this->name),
            ]);
        }
    }
}

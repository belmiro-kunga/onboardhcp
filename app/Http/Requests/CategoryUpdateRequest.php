<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Validation\Rule;

class CategoryUpdateRequest extends CategoryStoreRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $category = $this->route('category');
        
        return array_merge(parent::rules(), [
            'name' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('categories')
                    ->ignore($category->id)
                    ->whereNull('deleted_at'),
            ],
            'icon' => 'sometimes|nullable|string|max:50',
            'color' => [
                'sometimes',
                'nullable',
                'string',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            ],
            'icon_file' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,svg',
                'max:2048',
                'dimensions:max_width=512,max_height=512,ratio=1/1',
            ],
        ]);
    }
    
    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $category = $this->route('category');
        
        // Prevent changing the default category's name
        if ($category->id === config('app.default_category_id', 1)) {
            $validator->after(function ($validator) {
                if ($this->has('name')) {
                    $validator->errors()->add('name', 'The name of the default category cannot be changed.');
                }
            });
        }
    }
}

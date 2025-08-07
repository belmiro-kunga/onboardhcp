<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryBulkActionRequest extends FormRequest
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
            'action' => [
                'required',
                'string',
                Rule::in(['delete', 'activate', 'deactivate', 'feature', 'unfeature']),
            ],
            'ids' => [
                'required',
                'array',
                'min:1',
            ],
            'ids.*' => [
                'required',
                'integer',
                'exists:categories,id',
                function ($attribute, $value, $fail) {
                    // Prevent modifying the default category
                    if ((int)$value === (int)config('app.default_category_id', 1)) {
                        $fail('The default category cannot be modified in bulk actions.');
                    }
                },
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
            'action.required' => 'The action field is required.',
            'action.in' => 'The selected action is invalid. Valid actions are: delete, activate, deactivate, feature, unfeature.',
            'ids.required' => 'Please select at least one category.',
            'ids.array' => 'The selected categories must be an array.',
            'ids.min' => 'Please select at least one category.',
            'ids.*.exists' => 'One or more selected categories do not exist.',
        ];
    }
}

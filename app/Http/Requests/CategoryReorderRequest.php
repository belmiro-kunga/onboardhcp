<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryReorderRequest extends FormRequest
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
        $categoryIds = Category::pluck('id')->toArray();
        
        return [
            'order' => [
                'required',
                'array',
                'size:' . count($categoryIds),
                Rule::in($categoryIds),
            ],
            'order.*' => [
                'required',
                'integer',
                'distinct',
                Rule::in($categoryIds),
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
            'order.required' => 'The order array is required.',
            'order.array' => 'The order must be an array of category IDs.',
            'order.size' => 'The order array must contain all category IDs exactly once.',
            'order.in' => 'The order array contains invalid category IDs.',
            'order.*.required' => 'Each position in the order array is required.',
            'order.*.integer' => 'Each position in the order array must be an integer.',
            'order.*.distinct' => 'The order array contains duplicate category IDs.',
            'order.*.in' => 'The order array contains invalid category IDs.',
        ];
    }
}

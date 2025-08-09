<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('edit_roles');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('roles')->ignore($this->role->id)],
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,slug'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome do role é obrigatório.',
            'name.unique' => 'Já existe um role com este nome.',
            'name.max' => 'O nome do role não pode ter mais de 100 caracteres.',
            'description.max' => 'A descrição não pode ter mais de 500 caracteres.',
            'permissions.array' => 'As permissões devem ser fornecidas como um array.',
            'permissions.*.exists' => 'Uma ou mais permissões selecionadas são inválidas.'
        ];
    }
}
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create_users');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:user_groups,name',
            'description' => 'nullable|string|max:500',
            'type' => 'required|in:department,team,custom',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,slug',
            'users' => 'array',
            'users.*' => 'exists:users,id'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome do grupo é obrigatório.',
            'name.unique' => 'Já existe um grupo com este nome.',
            'name.max' => 'O nome do grupo não pode ter mais de 100 caracteres.',
            'description.max' => 'A descrição não pode ter mais de 500 caracteres.',
            'type.required' => 'O tipo do grupo é obrigatório.',
            'type.in' => 'O tipo do grupo deve ser: departamento, equipa ou personalizado.',
            'permissions.array' => 'As permissões devem ser fornecidas como um array.',
            'permissions.*.exists' => 'Uma ou mais permissões selecionadas são inválidas.',
            'users.array' => 'Os utilizadores devem ser fornecidos como um array.',
            'users.*.exists' => 'Um ou mais utilizadores selecionados são inválidos.'
        ];
    }
}
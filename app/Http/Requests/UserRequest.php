<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->is_admin;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userId = $this->route('user') ? $this->route('user')->id : null;
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'password' => [
                $isUpdate ? 'nullable' : 'required',
                'string',
                'min:8',
                'confirmed'
            ],
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'hire_date' => 'nullable|date',
            'birth_date' => 'nullable|date|before:today',
            'status' => [
                'nullable',
                Rule::in(['active', 'inactive', 'pending', 'blocked', 'suspended'])
            ],
            'is_admin' => 'boolean',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'send_welcome_email' => 'boolean'
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ter um formato válido.',
            'email.unique' => 'Este email já está sendo usado por outro usuário.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'phone.max' => 'O telefone não pode ter mais de 20 caracteres.',
            'department.max' => 'O departamento não pode ter mais de 100 caracteres.',
            'position.max' => 'O cargo não pode ter mais de 100 caracteres.',
            'hire_date.date' => 'A data de admissão deve ser uma data válida.',
            'birth_date.date' => 'A data de nascimento deve ser uma data válida.',
            'birth_date.before' => 'A data de nascimento deve ser anterior a hoje.',
            'status.in' => 'O status selecionado é inválido.',
            'avatar.image' => 'O avatar deve ser uma imagem.',
            'avatar.mimes' => 'O avatar deve ser um arquivo do tipo: jpeg, png, jpg, gif.',
            'avatar.max' => 'O avatar não pode ser maior que 2MB.',
            'roles.array' => 'Os roles devem ser um array.',
            'roles.*.exists' => 'Um ou mais roles selecionados são inválidos.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'email',
            'password' => 'senha',
            'phone' => 'telefone',
            'department' => 'departamento',
            'position' => 'cargo',
            'hire_date' => 'data de admissão',
            'birth_date' => 'data de nascimento',
            'status' => 'status',
            'is_admin' => 'administrador',
            'avatar' => 'avatar',
            'roles' => 'roles'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert string boolean values to actual booleans
        if ($this->has('is_admin')) {
            $this->merge([
                'is_admin' => filter_var($this->is_admin, FILTER_VALIDATE_BOOLEAN)
            ]);
        }

        if ($this->has('send_welcome_email')) {
            $this->merge([
                'send_welcome_email' => filter_var($this->send_welcome_email, FILTER_VALIDATE_BOOLEAN)
            ]);
        }

        // Set default status if not provided
        if (!$this->has('status') || empty($this->status)) {
            $this->merge(['status' => 'active']);
        }
    }
}

<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserManagementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->route('user')?->id)],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,id'],
            'verify_email' => ['nullable', 'boolean'],
            'unverify_email' => ['nullable', 'boolean'],
            'generate_new_password' => ['nullable', 'boolean'],
        ];
    }
}

<?php

namespace App\Http\Requests\Web;

use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Role;

class StoreUserManagementRequest extends FormRequest
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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'roles' => ['array'],
            'roles.*' => ['exists:roles,id'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check if any of the roles being assigned have sensitive permissions
            if ($this->has('roles') && is_array($this->roles)) {
                $roles = Role::whereIn('id', $this->roles)->get();
                $sensitivePermissions = Permission::sensitivePermissions();

                foreach ($roles as $role) {
                    foreach ($sensitivePermissions as $permission) {
                        if ($role->hasPermissionTo($permission)) {
                            // New users cannot be created with sensitive permissions
                            // They must enable MFA first
                            $validator->errors()->add(
                                'roles',
                                __('Cannot assign roles with sensitive permissions (manage users, manage roles, assign roles, or manage settings) to a new user. The user must first enable multi-factor authentication (MFA) before receiving these permissions.')
                            );

                            return;
                        }
                    }
                }
            }
        });
    }
}

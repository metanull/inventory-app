<?php

namespace App\Http\Requests\Web;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

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

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->route('user');

            if (! $user instanceof User) {
                return;
            }

            // Check if any of the roles being assigned have sensitive permissions
            if ($this->has('roles') && is_array($this->roles)) {
                $roles = Role::whereIn('id', $this->roles)->get();
                $sensitivePermissions = Permission::sensitivePermissions();

                foreach ($roles as $role) {
                    foreach ($sensitivePermissions as $permission) {
                        if ($role->hasPermissionTo($permission)) {
                            // User must have MFA enabled to receive this role
                            if (! $user->hasTwoFactorEnabled()) {
                                $validator->errors()->add(
                                    'roles',
                                    __('The user must have multi-factor authentication (MFA) enabled before being assigned roles with sensitive permissions (manage users, manage roles, assign roles, or manage settings).')
                                );

                                return;
                            }

                            break 2; // Exit both loops once we find one sensitive permission
                        }
                    }
                }
            }
        });
    }
}

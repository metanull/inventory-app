<?php

namespace App\Actions\Fortify;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Spatie\Permission\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        // Check if self-registration is enabled
        if (! Setting::get('self_registration_enabled', false)) {
            throw ValidationException::withMessages([
                'registration' => ['Self-registration is currently disabled. Please contact an administrator.'],
            ]);
        }

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
            'preferred_2fa_method' => ['nullable', 'string', 'in:totp,email'],
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'preferred_2fa_method' => $input['preferred_2fa_method'] ?? 'totp',
        ]);

        // Assign "Non-verified users" role to newly registered users
        $nonVerifiedRole = Role::findByName('Non-verified users');
        if ($nonVerifiedRole) {
            $user->assignRole($nonVerifiedRole);
        }

        return $user;
    }
}

<?php

namespace Tests\Unit\Requests\Web;

use App\Enums\Permission;
use App\Http\Requests\Web\UpdateUserManagementRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Tests for UpdateUserManagementRequest MFA/sensitive permissions validation.
 */
class UpdateUserManagementRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_sensitive_role_for_user_without_mfa(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(Permission::MANAGE_USERS->value);

        $request = new UpdateUserManagementRequest;
        $request->setRouteResolver(function () use ($user) {
            return new class($user)
            {
                public function __construct(private User $user) {}

                public function parameter($key)
                {
                    return $key === 'user' ? $this->user : null;
                }
            };
        });
        $request->merge([
            'name' => $user->name,
            'email' => $user->email,
            'roles' => [$role->id],
        ]);

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        try {
            $validator->validate();
            $this->fail('Validation should have failed for user without MFA');
        } catch (ValidationException $e) {
            $this->assertTrue($validator->errors()->has('roles'));
            $this->assertStringContainsString('multi-factor authentication', $validator->errors()->first('roles'));
        }
    }

    public function test_accepts_sensitive_role_for_user_with_mfa(): void
    {
        $user = User::factory()->create([
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
            'two_factor_confirmed_at' => now(),
        ]);

        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(Permission::MANAGE_USERS->value);

        $request = new UpdateUserManagementRequest;
        $request->setRouteResolver(function () use ($user) {
            return new class($user)
            {
                public function __construct(private User $user) {}

                public function parameter($key)
                {
                    return $key === 'user' ? $this->user : null;
                }
            };
        });
        $request->merge([
            'name' => $user->name,
            'email' => $user->email,
            'roles' => [$role->id],
        ]);

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        $validator->validate();
        $this->assertFalse($validator->errors()->any());
    }

    public function test_accepts_non_sensitive_role_for_user_without_mfa(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'editor']);
        $role->givePermissionTo(Permission::VIEW_DATA->value);

        $request = new UpdateUserManagementRequest;
        $request->setRouteResolver(function () use ($user) {
            return new class($user)
            {
                public function __construct(private User $user) {}

                public function parameter($key)
                {
                    return $key === 'user' ? $this->user : null;
                }
            };
        });
        $request->merge([
            'name' => $user->name,
            'email' => $user->email,
            'roles' => [$role->id],
        ]);

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        $validator->validate();
        $this->assertFalse($validator->errors()->any());
    }
}

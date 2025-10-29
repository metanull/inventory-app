<?php

namespace Tests\Unit\Requests\Web;

use App\Enums\Permission;
use App\Http\Requests\Web\StoreUserManagementRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Tests for StoreUserManagementRequest MFA/sensitive permissions validation.
 *
 * Tests that new users cannot be assigned roles with sensitive permissions
 * without MFA being enabled first.
 */
class StoreUserManagementRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_role_with_manage_users_permission(): void
    {
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(Permission::MANAGE_USERS->value);

        $request = new StoreUserManagementRequest;
        $request->merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'roles' => [$role->id],
        ]);

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        try {
            $validator->validate();
            $this->fail('Validation should have failed for sensitive permission');
        } catch (ValidationException $e) {
            $this->assertTrue($validator->errors()->has('roles'));
            $this->assertStringContainsString('multi-factor authentication', $validator->errors()->first('roles'));
        }
    }

    public function test_rejects_role_with_manage_roles_permission(): void
    {
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(Permission::MANAGE_ROLES->value);

        $request = new StoreUserManagementRequest;
        $request->merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'roles' => [$role->id],
        ]);

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        try {
            $validator->validate();
            $this->fail('Validation should have failed for sensitive permission');
        } catch (ValidationException $e) {
            $this->assertTrue($validator->errors()->has('roles'));
        }
    }

    public function test_rejects_role_with_assign_roles_permission(): void
    {
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(Permission::ASSIGN_ROLES->value);

        $request = new StoreUserManagementRequest;
        $request->merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'roles' => [$role->id],
        ]);

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        try {
            $validator->validate();
            $this->fail('Validation should have failed for sensitive permission');
        } catch (ValidationException $e) {
            $this->assertTrue($validator->errors()->has('roles'));
        }
    }

    public function test_rejects_role_with_manage_settings_permission(): void
    {
        $role = Role::create(['name' => 'admin']);
        $role->givePermissionTo(Permission::MANAGE_SETTINGS->value);

        $request = new StoreUserManagementRequest;
        $request->merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'roles' => [$role->id],
        ]);

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        try {
            $validator->validate();
            $this->fail('Validation should have failed for sensitive permission');
        } catch (ValidationException $e) {
            $this->assertTrue($validator->errors()->has('roles'));
        }
    }

    public function test_accepts_role_with_non_sensitive_permissions(): void
    {
        $role = Role::create(['name' => 'editor']);
        $role->givePermissionTo(Permission::VIEW_DATA->value);
        $role->givePermissionTo(Permission::CREATE_DATA->value);

        $request = new StoreUserManagementRequest;
        $request->merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'roles' => [$role->id],
        ]);

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        $validator->validate();
        $this->assertFalse($validator->errors()->any());
    }

    public function test_accepts_user_without_roles(): void
    {
        $request = new StoreUserManagementRequest;
        $request->merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        $validator->validate();
        $this->assertFalse($validator->errors()->any());
    }
}

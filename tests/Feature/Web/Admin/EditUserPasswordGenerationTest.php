<?php

namespace Tests\Feature\Web\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EditUserPasswordGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles and permissions exist
        $this->artisan('db:seed', ['--class' => 'ProductionDataSeeder']);
    }

    public function test_admin_can_generate_new_password_for_user(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $targetUser = User::factory()->create(['email_verified_at' => now()]);
        $targetUser->assignRole('Regular User');

        $originalPassword = $targetUser->password;

        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'generate_new_password' => '1',
            'roles' => [$targetUser->roles->first()->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('generated_password');
        $response->assertSessionHas('user_name', $targetUser->name);
        $response->assertSessionHas('user_email', $targetUser->email);

        // Verify password was changed
        $targetUser->refresh();
        $this->assertNotEquals($originalPassword, $targetUser->password);

        // Verify user can authenticate with new password
        $generatedPassword = session('generated_password');
        $this->assertTrue(Hash::check($generatedPassword, $targetUser->password));
    }

    public function test_admin_can_update_user_without_generating_new_password(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $targetUser = User::factory()->create(['email_verified_at' => now()]);
        $targetUser->assignRole('Regular User');

        $originalPassword = $targetUser->password;

        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => 'Updated Name',
            'email' => $targetUser->email,
            'roles' => [$targetUser->roles->first()->id],
            // No generate_new_password checkbox
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');
        $response->assertSessionMissing('generated_password');

        // Verify password was NOT changed
        $targetUser->refresh();
        $this->assertEquals($originalPassword, $targetUser->password);
        $this->assertEquals('Updated Name', $targetUser->name);
    }

    public function test_generated_password_is_secure_and_different_each_time(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $targetUser = User::factory()->create(['email_verified_at' => now()]);
        $targetUser->assignRole('Regular User');

        // Generate first password
        $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'generate_new_password' => '1',
            'roles' => [$targetUser->roles->first()->id],
        ]);

        $firstPassword = session('generated_password');
        $firstHash = $targetUser->fresh()->password;

        // Generate second password
        $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'generate_new_password' => '1',
            'roles' => [$targetUser->roles->first()->id],
        ]);

        $secondPassword = session('generated_password');
        $secondHash = $targetUser->fresh()->password;

        // Verify passwords are different
        $this->assertNotEquals($firstPassword, $secondPassword);
        $this->assertNotEquals($firstHash, $secondHash);

        // Verify both passwords meet security requirements
        $this->assertGreaterThanOrEqual(16, strlen($firstPassword));
        $this->assertGreaterThanOrEqual(16, strlen($secondPassword));

        // Verify both passwords work
        $this->assertTrue(Hash::check($firstPassword, $firstHash));
        $this->assertTrue(Hash::check($secondPassword, $secondHash));
    }

    public function test_edit_form_shows_password_generation_option(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $targetUser = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($admin)->get(route('admin.users.edit', $targetUser));

        $response->assertStatus(200);
        $response->assertSee('Password Management');
        $response->assertSee('Generate New Password');
        $response->assertSee('Check this box to generate a new secure password');
        $response->assertDontSee('type="password"'); // Should not have manual password input fields
    }

    public function test_password_generation_respects_user_permissions(): void
    {
        $regularUser = User::factory()->create(['email_verified_at' => now()]);
        $regularUser->assignRole('Regular User');

        $targetUser = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($regularUser)->put(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'generate_new_password' => '1',
        ]);

        $response->assertStatus(403);
    }

    public function test_password_generation_notification_displays_in_index(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $targetUser = User::factory()->create(['email_verified_at' => now()]);
        $targetUser->assignRole('Regular User');

        // Generate password
        $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'generate_new_password' => '1',
            'roles' => [$targetUser->roles->first()->id],
        ]);

        // Check notification appears in index
        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Password Generated Successfully');
        $response->assertSee($targetUser->name);
        $response->assertSee($targetUser->email);
        $response->assertSee('Generated Password:');
        $response->assertSee(session('generated_password'));
    }

    public function test_admin_can_update_user_roles_with_password_generation(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $targetUser = User::factory()->create(['email_verified_at' => now()]);
        $targetUser->assignRole('Regular User');

        $managerRole = Role::where('name', 'Manager of Users')->first();

        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'generate_new_password' => '1',
            'roles' => [$managerRole->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('generated_password');

        // Verify both role update and password generation worked
        $targetUser->refresh();
        $this->assertTrue($targetUser->hasRole('Manager of Users'));
        $this->assertFalse($targetUser->hasRole('Regular User'));

        $generatedPassword = session('generated_password');
        $this->assertTrue(Hash::check($generatedPassword, $targetUser->password));
    }

    public function test_email_verification_works_with_password_generation(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Manager of Users');

        $targetUser = User::factory()->create(['email_verified_at' => null]); // Unverified
        $targetUser->assignRole('Regular User');

        $response = $this->actingAs($admin)->put(route('admin.users.update', $targetUser), [
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'generate_new_password' => '1',
            'verify_email' => '1',
            'roles' => [$targetUser->roles->first()->id],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('generated_password');

        // Verify both email verification and password generation worked
        $targetUser->refresh();
        $this->assertNotNull($targetUser->email_verified_at);

        $generatedPassword = session('generated_password');
        $this->assertTrue(Hash::check($generatedPassword, $targetUser->password));
    }
}

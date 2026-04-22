<?php

namespace Tests\Web\Pages;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Web\Traits\RendersShowPageUnderRealisticDataset;

/**
 * Realistic-dataset HTTP test for GET /web/admin/roles/{id}.
 *
 * Seeds a production-like number of permissions assigned to the role and
 * users carrying it to verify no full-table snapshot bloat.
 */
class RoleShowRealisticDatasetTest extends TestCase
{
    use RefreshDatabase;
    use RendersShowPageUnderRealisticDataset;

    /**
     * 20 covers auth overhead, role fetch, and permissions + users eager-loads.
     * The page does not embed full-table option snapshots.
     */
    protected function queryBudget(): int
    {
        return 20;
    }

    /**
     * 500 KB ceiling — the show page lists permissions and users in plain HTML.
     */
    protected function responseSizeBudget(): int
    {
        return 500_000;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $manager = User::factory()->create(['email_verified_at' => now()]);
        $manager->givePermissionTo(Permission::MANAGE_ROLES);
        $this->actingAs($manager);
    }

    protected function getShowRouteName(): string
    {
        return 'admin.roles.show';
    }

    protected function seedRealisticDataset(): Model
    {
        $subject = Role::create(['name' => 'Benchmark Role', 'guard_name' => 'web']);

        // ≥ 200 sibling roles to verify no full-table load
        for ($i = 0; $i < 200; $i++) {
            Role::create(['name' => "Sibling Role {$i}", 'guard_name' => 'web']);
        }

        // Assign all existing permissions to the subject role
        $permissions = SpatiePermission::all();
        $subject->syncPermissions($permissions);

        // ≥ 20 users assigned to the subject role
        $users = User::factory()->count(20)->create();
        foreach ($users as $user) {
            $user->assignRole($subject);
        }

        return $subject;
    }
}

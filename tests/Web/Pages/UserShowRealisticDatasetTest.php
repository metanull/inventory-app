<?php

namespace Tests\Web\Pages;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Web\Traits\RendersShowPageUnderRealisticDataset;

/**
 * Realistic-dataset HTTP test for GET /web/admin/users/{id}.
 *
 * Seeds a production-like number of roles (with permissions) assigned to the
 * subject user to verify no full-table snapshot bloat.
 */
class UserShowRealisticDatasetTest extends TestCase
{
    use RefreshDatabase;
    use RendersShowPageUnderRealisticDataset;

    /**
     * 20 covers auth overhead, user fetch, and roles.permissions eager-loads.
     * The page does not embed full-table option snapshots.
     */
    protected function queryBudget(): int
    {
        return 20;
    }

    /**
     * 500 KB ceiling — the show page lists roles and permissions in plain HTML.
     */
    protected function responseSizeBudget(): int
    {
        return 500_000;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $manager = User::factory()->create(['email_verified_at' => now()]);
        $manager->givePermissionTo(Permission::MANAGE_USERS);
        $this->actingAs($manager);
    }

    protected function getShowRouteName(): string
    {
        return 'admin.users.show';
    }

    protected function seedRealisticDataset(): Model
    {
        $subject = User::factory()->create(['email_verified_at' => now()]);

        // ≥ 200 sibling users to verify no full-table load
        User::factory()->count(200)->create();

        // Create roles (with permissions) and assign them to the subject
        $roleA = Role::create(['name' => 'Role Alpha', 'guard_name' => 'web']);
        $roleB = Role::create(['name' => 'Role Beta', 'guard_name' => 'web']);

        $subject->assignRole([$roleA, $roleB]);

        return $subject;
    }
}

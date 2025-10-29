<?php

namespace Tests\Web\Middleware;

use App\Enums\Permission;
use App\Models\Collection;
use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\TestsWebPermissions;

/**
 * Test that Web routes properly enforce permission middleware
 *
 * This test uses SAMPLING approach - tests representative routes to verify permissions are enforced,
 * rather than testing every single route exhaustively.
 */
class PermissionsTest extends TestCase
{
    use RefreshDatabase;
    use TestsWebPermissions;

    /**
     * Data provider for Web routes and their required permissions (sample coverage)
     *
     * Tests one route per permission type to verify permission middleware works
     */
    public static function routePermissionsProvider(): array
    {
        return [
            // VIEW_DATA permission - these all work
            ['GET', 'items.index', Permission::VIEW_DATA, null],
            ['GET', 'collections.show', Permission::VIEW_DATA, Collection::class],
            ['GET', 'glossaries.index', Permission::VIEW_DATA, null],
            ['GET', 'partners.show', Permission::VIEW_DATA, Partner::class],

            // MANAGE_USERS permission - admin user management routes
            ['GET', 'admin.users.index', Permission::MANAGE_USERS, null],
            ['GET', 'admin.users.create', Permission::MANAGE_USERS, null],
            ['POST', 'admin.users.store', Permission::MANAGE_USERS, null],
            ['PATCH', 'admin.users.update', Permission::MANAGE_USERS, \App\Models\User::class],
            ['DELETE', 'admin.users.destroy', Permission::MANAGE_USERS, \App\Models\User::class],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('routePermissionsProvider')]
    public function test_route_enforces_correct_permission(
        string $method,
        string $route,
        Permission $requiredPermission,
        ?string $modelClass
    ): void {
        $routeParams = $this->getRouteParams($modelClass);

        $this->assertRouteRequiresPermission(
            $method,
            $route,
            $requiredPermission,
            $routeParams
        );
    }
}

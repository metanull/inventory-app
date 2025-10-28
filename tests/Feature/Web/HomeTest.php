<?php

declare(strict_types=1);

namespace Tests\Feature\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class HomeTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    public function test_home_tiles_render_for_authenticated_user(): void
    {
        $this->actingAsDataUser();

        $response = $this->get('/web');
        $response->assertOk();
        $response->assertSee('Inventory Portal');
        $response->assertSee('Items');
        $response->assertSee('Partners');
        $response->assertSee('API Documentation');
        if (config('interface.show_spa_link')) {
            $response->assertSee('SPA Client');
        } else {
            $response->assertDontSee('SPA Client');
        }
    }
}

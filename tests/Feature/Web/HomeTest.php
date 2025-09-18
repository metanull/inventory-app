<?php

declare(strict_types=1);

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_tiles_render_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        /** @var \Illuminate\Contracts\Auth\Authenticatable $user */
        $this->actingAs($user);

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

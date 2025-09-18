<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Contexts;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_store_persists_context_and_redirects(): void
    {
        $payload = [
            'internal_name' => 'Test Context',
            'is_default' => true,
        ];

        $response = $this->post(route('contexts.store'), $payload);
        $response->assertRedirect();
        $this->assertDatabaseHas('contexts', [
            'internal_name' => 'Test Context',
            'is_default' => true,
        ]);
    }

    public function test_store_validation_errors(): void
    {
        $response = $this->post(route('contexts.store'), [
            'internal_name' => '',
            'is_default' => 'maybe',
        ]);
        $response->assertSessionHasErrors(['internal_name', 'is_default']);
    }
}

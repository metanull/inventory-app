<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Contexts;

use App\Models\Context;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_update_persists_changes_and_redirects(): void
    {
        $context = Context::factory()->create([
            'internal_name' => 'Old C',
            'is_default' => false,
        ]);

        $response = $this->put(route('contexts.update', $context), [
            'internal_name' => 'New C',
            'is_default' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('contexts', [
            'id' => $context->id,
            'internal_name' => 'New C',
            'is_default' => true,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Contexts;

use App\Models\Context;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_show_displays_core_fields(): void
    {
        $context = Context::factory()->create([
            'internal_name' => 'Alpha Context',
            'backward_compatibility' => 'LEG-CTX',
            'is_default' => true,
        ]);

        $response = $this->get(route('contexts.show', $context));
        $response->assertOk();
        $response->assertSee('Alpha Context');
        $response->assertSee('Legacy: LEG-CTX');
        $response->assertSee('Information');
    }
}

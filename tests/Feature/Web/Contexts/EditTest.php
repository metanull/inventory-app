<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Contexts;

use App\Models\Context;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_edit_form_renders(): void
    {
        $context = Context::factory()->create();
        $response = $this->get(route('contexts.edit', $context));
        $response->assertOk();
        $response->assertSee('Edit Context');
    }
}

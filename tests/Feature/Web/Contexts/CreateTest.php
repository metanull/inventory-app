<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Contexts;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_create_form_renders(): void
    {
        $response = $this->get(route('contexts.create'));
        $response->assertOk();
        $response->assertSee('Create Context');
        $response->assertSee('Internal Name');
    }
}

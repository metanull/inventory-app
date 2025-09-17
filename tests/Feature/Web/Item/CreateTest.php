<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Item;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_create_form_renders(): void
    {
        $response = $this->get(route('items.create'));
        $response->assertOk();
        $response->assertSee('Create Item');
        $response->assertSee('Internal Name');
    }
}

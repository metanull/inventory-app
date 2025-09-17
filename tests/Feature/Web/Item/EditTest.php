<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Item;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EditTest extends TestCase
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

    public function test_edit_form_renders(): void
    {
        $item = Item::factory()->create();
        $response = $this->get(route('items.edit', $item));
        $response->assertOk();
        $response->assertSee('Edit Item');
        $response->assertSee(e($item->internal_name));
    }
}

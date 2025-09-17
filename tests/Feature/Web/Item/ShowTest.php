<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Item;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowTest extends TestCase
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

    public function test_show_displays_item_information(): void
    {
        $item = Item::factory()->create();

        $response = $this->get(route('items.show', $item));
        $response->assertOk();
        $response->assertSee(e($item->internal_name));
        if ($item->backward_compatibility) {
            $response->assertSee(e($item->backward_compatibility));
        }
    }

    public function test_show_nonexistent_returns_404(): void
    {
        $this->get('/web/items/nonexistent-uuid')->assertNotFound();
    }
}

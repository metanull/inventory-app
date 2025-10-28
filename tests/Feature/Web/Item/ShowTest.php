<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Item;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class ShowTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsDataUser();
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

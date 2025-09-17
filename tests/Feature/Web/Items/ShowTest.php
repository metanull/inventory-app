<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Items;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_item_show_page_displays_core_fields(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $item = Item::factory()->create(['backward_compatibility' => 'LEG123']);

        $this->actingAs($user);
        $response = $this->get(route('items.show', $item));
        $response->assertOk();
        $response->assertSee($item->internal_name);
        $response->assertSee('Legacy: LEG123');
        $response->assertSee('Information');
    }
}

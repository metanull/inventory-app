<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Items;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_items_index_renders_and_lists_items(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        Item::factory()->count(3)->create();

        $this->actingAs($user);
        $response = $this->get(route('items.index'));
        $response->assertOk();
        $response->assertSee('Items');
        $response->assertSee('Add Item');
        // Column headers
        $response->assertSee('Internal Name');
        $response->assertSee('Backward Compatibility');
        $response->assertSee('Search');
    }

    public function test_items_index_search_filters_results(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        Item::factory()->create(['internal_name' => 'ALPHA_TEST']);
        Item::factory()->create(['internal_name' => 'BETA_SAMPLE']);

        $this->actingAs($user);
        $response = $this->get(route('items.index', ['q' => 'ALPHA']));
        $response->assertOk();
        $response->assertSee('ALPHA_TEST');
        $response->assertDontSee('BETA_SAMPLE');
    }
}

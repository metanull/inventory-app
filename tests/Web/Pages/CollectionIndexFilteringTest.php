<?php

namespace Tests\Web\Pages;

use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

/**
 * HTTP GET tests for Collections index filtering and sorting via query parameters.
 */
class CollectionIndexFilteringTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_search_shows_matching_collections(): void
    {
        Collection::factory()->create(['internal_name' => 'Alpha Collection']);
        Collection::factory()->create(['internal_name' => 'Beta Collection']);

        $response = $this->get(route('collections.index', ['q' => 'Alpha']));

        $response->assertOk()
            ->assertSee('Alpha Collection')
            ->assertDontSee('Beta Collection');
    }

    public function test_hierarchy_mode_shows_only_root_collections(): void
    {
        $root = Collection::factory()->create(['internal_name' => 'Root Collection']);
        Collection::factory()->create([
            'internal_name' => 'Child Collection',
            'parent_id' => $root->id,
            'language_id' => $root->language_id,
            'context_id' => $root->context_id,
        ]);

        $response = $this->get(route('collections.index', ['hierarchy' => '1']));

        $response->assertOk()
            ->assertSee('Root Collection')
            ->assertDontSee('Child Collection');
    }

    public function test_flat_mode_shows_all_collections(): void
    {
        $root = Collection::factory()->create(['internal_name' => 'Root Collection']);
        Collection::factory()->create([
            'internal_name' => 'Child Collection',
            'parent_id' => $root->id,
            'language_id' => $root->language_id,
            'context_id' => $root->context_id,
        ]);

        $response = $this->get(route('collections.index', ['hierarchy' => '0']));

        $response->assertOk()
            ->assertSee('Root Collection')
            ->assertSee('Child Collection');
    }

    public function test_parent_drill_down_shows_only_children(): void
    {
        $root = Collection::factory()->create(['internal_name' => 'Root Collection']);
        Collection::factory()->create([
            'internal_name' => 'Child Collection',
            'parent_id' => $root->id,
            'language_id' => $root->language_id,
            'context_id' => $root->context_id,
        ]);
        Collection::factory()->create(['internal_name' => 'Other Root']);

        $response = $this->get(route('collections.index', ['parent_id' => $root->id, 'hierarchy' => '1']));

        $response->assertOk()
            ->assertSee('Child Collection')
            ->assertDontSee('Other Root');
    }

    public function test_sort_by_internal_name_asc(): void
    {
        Collection::factory()->create(['internal_name' => 'Bravo Collection']);
        Collection::factory()->create(['internal_name' => 'Alpha Collection']);

        $response = $this->get(route('collections.index', ['sort' => 'internal_name', 'dir' => 'asc', 'hierarchy' => '0']));

        $response->assertOk();
        $content = $response->getContent();
        $alphaPos = strpos($content, 'Alpha Collection');
        $bravoPos = strpos($content, 'Bravo Collection');
        $this->assertNotFalse($alphaPos);
        $this->assertNotFalse($bravoPos);
        $this->assertLessThan($bravoPos, $alphaPos);
    }

    public function test_sort_by_display_order(): void
    {
        Collection::factory()->create(['internal_name' => 'Collection A', 'display_order' => 2]);
        Collection::factory()->create(['internal_name' => 'Collection B', 'display_order' => 1]);

        $response = $this->get(route('collections.index', ['sort' => 'display_order', 'dir' => 'asc', 'hierarchy' => '0']));

        $response->assertOk();
    }

    public function test_unknown_sort_field_is_ignored_and_page_loads(): void
    {
        Collection::factory()->count(3)->create();

        $response = $this->get(route('collections.index', ['sort' => 'nonexistent_column', 'hierarchy' => '0']));

        $response->assertOk();
    }

    public function test_unknown_sort_direction_is_ignored_and_page_loads(): void
    {
        Collection::factory()->count(3)->create();

        $response = $this->get(route('collections.index', ['sort' => 'internal_name', 'dir' => 'sideways', 'hierarchy' => '0']));

        $response->assertOk();
    }
}

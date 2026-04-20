<?php

namespace Tests\Web\Pages;

use App\Models\Item;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

/**
 * HTTP GET tests for Items index filtering and sorting via query parameters.
 */
class ItemIndexFilteringTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_search_shows_matching_items(): void
    {
        Item::factory()->create(['internal_name' => 'Alpha Item']);
        Item::factory()->create(['internal_name' => 'Beta Item']);

        $response = $this->get(route('items.index', ['q' => 'Alpha']));

        $response->assertOk()
            ->assertSee('Alpha Item')
            ->assertDontSee('Beta Item');
    }

    public function test_type_filter_shows_matching_items(): void
    {
        Item::factory()->create(['internal_name' => 'Test Object', 'type' => 'object']);
        Item::factory()->create(['internal_name' => 'Test Monument', 'type' => 'monument']);

        $response = $this->get(route('items.index', ['type' => 'object', 'hierarchy' => '0']));

        $response->assertOk()
            ->assertSee('Test Object')
            ->assertDontSee('Test Monument');
    }

    public function test_tag_filter_shows_tagged_items(): void
    {
        $tag = Tag::factory()->create(['internal_name' => 'FilterTag']);
        $tagged = Item::factory()->create(['internal_name' => 'Tagged Item']);
        $tagged->tags()->attach($tag);
        Item::factory()->create(['internal_name' => 'Untagged Item']);

        $response = $this->get(route('items.index', ['tags' => [$tag->id], 'hierarchy' => '0']));

        $response->assertOk()
            ->assertSee('Tagged Item')
            ->assertDontSee('Untagged Item');
    }

    public function test_hierarchy_mode_shows_only_root_items(): void
    {
        $root = Item::factory()->create(['internal_name' => 'Root Item']);
        Item::factory()->create(['internal_name' => 'Child Item', 'parent_id' => $root->id]);

        $response = $this->get(route('items.index', ['hierarchy' => '1']));

        $response->assertOk()
            ->assertSee('Root Item')
            ->assertDontSee('Child Item');
    }

    public function test_flat_mode_shows_all_items(): void
    {
        $root = Item::factory()->create(['internal_name' => 'Root Item']);
        Item::factory()->create(['internal_name' => 'Child Item', 'parent_id' => $root->id]);

        $response = $this->get(route('items.index', ['hierarchy' => '0']));

        $response->assertOk()
            ->assertSee('Root Item')
            ->assertSee('Child Item');
    }

    public function test_parent_drill_down_shows_only_children(): void
    {
        $root = Item::factory()->create(['internal_name' => 'Root Item']);
        Item::factory()->create(['internal_name' => 'Child Item', 'parent_id' => $root->id]);
        Item::factory()->create(['internal_name' => 'Other Root']);

        $response = $this->get(route('items.index', ['parent_id' => $root->id, 'hierarchy' => '1']));

        $response->assertOk()
            ->assertSee('Child Item')
            ->assertDontSee('Other Root');
    }

    public function test_sort_by_internal_name_asc(): void
    {
        Item::factory()->create(['internal_name' => 'Bravo Item']);
        Item::factory()->create(['internal_name' => 'Alpha Item']);

        $response = $this->get(route('items.index', ['sort' => 'internal_name', 'dir' => 'asc', 'hierarchy' => '0']));

        $response->assertOk();
        $content = $response->getContent();
        $alphaPos = strpos($content, 'Alpha Item');
        $bravoPos = strpos($content, 'Bravo Item');
        $this->assertNotFalse($alphaPos);
        $this->assertNotFalse($bravoPos);
        $this->assertLessThan($bravoPos, $alphaPos, 'Alpha should appear before Bravo when sorting asc');
    }

    public function test_unknown_sort_field_is_ignored_and_page_loads(): void
    {
        Item::factory()->count(3)->create();

        $response = $this->get(route('items.index', ['sort' => 'nonexistent_column', 'hierarchy' => '0']));

        $response->assertOk();
    }

    public function test_unknown_sort_direction_is_ignored_and_page_loads(): void
    {
        Item::factory()->count(3)->create();

        $response = $this->get(route('items.index', ['sort' => 'internal_name', 'dir' => 'sideways', 'hierarchy' => '0']));

        $response->assertOk();
    }
}

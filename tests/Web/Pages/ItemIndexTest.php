<?php

namespace Tests\Web\Pages;

use App\Models\Collection;
use App\Models\Country;
use App\Models\Item;
use App\Models\ItemTranslation;
use App\Models\Partner;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class ItemIndexTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_index_defaults_to_request_driven_hierarchy_mode(): void
    {
        $root = Item::factory()->create(['internal_name' => 'Root item']);
        Item::factory()->withParent($root)->create(['internal_name' => 'Hidden child']);

        $response = $this->get(route('items.index'));

        $response
            ->assertOk()
            ->assertViewIs('items.index')
            ->assertSee('Root item')
            ->assertDontSee('Hidden child');

        $this->assertStringNotContainsString('livewire:dynamic-component', $response->getContent());
        $this->assertStringNotContainsString('wire:click', $response->getContent());
    }

    public function test_index_can_search_across_item_translations(): void
    {
        $matching = Item::factory()->create(['internal_name' => 'Plain internal name']);
        $other = Item::factory()->create(['internal_name' => 'Other item']);

        ItemTranslation::factory()->forItem($matching->id)->create([
            'name' => 'Temple translation',
            'alternate_name' => 'Temple alias',
        ]);
        ItemTranslation::factory()->forItem($other->id)->create([
            'name' => 'Other translation',
        ]);

        $response = $this->get(route('items.index', ['q' => 'Temple', 'hierarchy' => false]));

        $response
            ->assertOk()
            ->assertSee('Plain internal name')
            ->assertDontSee('Other item');
    }

    public function test_index_can_filter_by_explicit_item_list_filters(): void
    {
        $partner = Partner::factory()->create(['internal_name' => 'Matching partner']);
        $collection = Collection::factory()->create(['internal_name' => 'Matching collection']);
        $project = Project::factory()->create(['internal_name' => 'Matching project']);
        $country = Country::factory()->create(['internal_name' => 'Matching country']);
        $tagA = Tag::factory()->create(['internal_name' => 'tag-a']);
        $tagB = Tag::factory()->create(['internal_name' => 'tag-b']);

        $matching = Item::factory()->create([
            'internal_name' => 'Matching item',
            'type' => 'object',
            'partner_id' => $partner->id,
            'collection_id' => $collection->id,
            'project_id' => $project->id,
            'country_id' => $country->id,
        ]);
        $matching->tags()->attach([$tagA->id, $tagB->id]);

        $wrongType = Item::factory()->create([
            'internal_name' => 'Wrong type',
            'type' => 'monument',
            'partner_id' => $partner->id,
            'collection_id' => $collection->id,
            'project_id' => $project->id,
            'country_id' => $country->id,
        ]);
        $wrongType->tags()->attach([$tagA->id, $tagB->id]);

        $missingTag = Item::factory()->create([
            'internal_name' => 'Missing tag',
            'type' => 'object',
            'partner_id' => $partner->id,
            'collection_id' => $collection->id,
            'project_id' => $project->id,
            'country_id' => $country->id,
        ]);
        $missingTag->tags()->attach([$tagA->id]);

        $response = $this->get(route('items.index', [
            'hierarchy' => false,
            'partner_id' => $partner->id,
            'collection_id' => $collection->id,
            'project_id' => $project->id,
            'country_id' => $country->id,
            'type' => 'object',
            'tags' => [$tagA->id, $tagB->id],
        ]));

        $response
            ->assertOk()
            ->assertSee('Matching item')
            ->assertDontSee('Wrong type')
            ->assertDontSee('Missing tag');
    }

    public function test_index_can_drill_down_and_render_hierarchy_breadcrumbs(): void
    {
        $root = Item::factory()->create(['internal_name' => 'Root item']);
        $child = Item::factory()->withParent($root)->create(['internal_name' => 'Child item']);
        Item::factory()->withParent($child)->create(['internal_name' => 'Grandchild item']);

        $response = $this->get(route('items.index', [
            'hierarchy' => true,
            'parent_id' => $root->id,
        ]));

        $response
            ->assertOk()
            ->assertSee('All Items')
            ->assertSee('Root item')
            ->assertSee('Child item')
            ->assertDontSee('Grandchild item');

        $this->assertSame('Root item', $response->viewData('breadcrumbs')[0]['label']);
    }

    public function test_index_can_render_flat_mode_sorted_by_internal_name(): void
    {
        $parent = Item::factory()->create(['internal_name' => 'Zulu item']);
        Item::factory()->withParent($parent)->create(['internal_name' => 'Alpha child']);

        $response = $this->get(route('items.index', [
            'hierarchy' => false,
            'sort' => 'internal_name',
            'direction' => 'asc',
        ]));

        $response
            ->assertOk()
            ->assertSeeInOrder(['Alpha child', 'Zulu item']);
    }

    public function test_index_preserves_query_strings_in_pagination_and_sort_links(): void
    {
        foreach (range(1, 11) as $index) {
            Item::factory()->create([
                'internal_name' => 'Alpha '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'type' => 'object',
            ]);
        }

        $response = $this->get(route('items.index', [
            'q' => 'Alpha',
            'hierarchy' => false,
            'type' => 'object',
            'per_page' => 10,
            'sort' => 'internal_name',
            'direction' => 'asc',
        ]));

        $response->assertOk();

        $paginator = $response->viewData('items');
        $nextPageUrl = $paginator->nextPageUrl();

        $this->assertNotNull($nextPageUrl);
        $this->assertStringContainsString('q=Alpha', $nextPageUrl);
        $this->assertStringContainsString('type=object', $nextPageUrl);
        $this->assertStringContainsString('per_page=10', $nextPageUrl);

        $this->assertStringContainsString(
            'href="http://localhost/web/items?type=object&amp;hierarchy=0&amp;q=Alpha&amp;per_page=10&amp;sort=created_at&amp;direction=asc&amp;page=1"',
            $response->getContent(),
        );
    }

    public function test_index_does_not_preload_full_partner_collection_project_country_tables(): void
    {
        $partner = Partner::factory()->create(['internal_name' => 'Solo Partner']);
        Partner::factory()->count(3)->create();

        $response = $this->get(route('items.index', [
            'hierarchy' => false,
            'partner_id' => $partner->id,
        ]));

        $response->assertOk();

        $this->assertArrayNotHasKey('partners', $response->viewData());
        $this->assertArrayNotHasKey('collections', $response->viewData());
        $this->assertArrayNotHasKey('projects', $response->viewData());
        $this->assertArrayNotHasKey('countries', $response->viewData());
        $this->assertArrayNotHasKey('availableTags', $response->viewData());
    }

    public function test_index_exposes_selected_option_variables_when_filter_is_active(): void
    {
        $partner = Partner::factory()->create(['internal_name' => 'Active Partner']);
        $collection = Collection::factory()->create(['internal_name' => 'Active Collection']);
        $project = Project::factory()->create(['internal_name' => 'Active Project']);
        $country = Country::factory()->create(['internal_name' => 'Active Country']);
        $tag = Tag::factory()->create(['internal_name' => 'active-tag']);
        $item = Item::factory()->create([
            'partner_id' => $partner->id,
            'collection_id' => $collection->id,
            'project_id' => $project->id,
            'country_id' => $country->id,
        ]);
        $item->tags()->attach($tag->id);

        $response = $this->get(route('items.index', [
            'hierarchy' => false,
            'partner_id' => $partner->id,
            'collection_id' => $collection->id,
            'project_id' => $project->id,
            'country_id' => $country->id,
            'tags' => [$tag->id],
        ]));

        $response->assertOk();

        $this->assertSame($partner->id, $response->viewData('selectedPartner')->id);
        $this->assertSame($collection->id, $response->viewData('selectedCollection')->id);
        $this->assertSame($project->id, $response->viewData('selectedProject')->id);
        $this->assertSame($country->id, $response->viewData('selectedCountry')->id);
        $this->assertSame($tag->id, $response->viewData('selectedTags')->first()->id);
    }

    public function test_index_exposes_null_selected_option_when_no_filter_is_active(): void
    {
        $response = $this->get(route('items.index'));

        $response->assertOk();

        $this->assertNull($response->viewData('selectedPartner'));
        $this->assertNull($response->viewData('selectedCollection'));
        $this->assertNull($response->viewData('selectedProject'));
        $this->assertNull($response->viewData('selectedCountry'));
        $this->assertTrue($response->viewData('selectedTags')->isEmpty());
    }
}

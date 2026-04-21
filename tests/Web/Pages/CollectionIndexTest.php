<?php

namespace Tests\Web\Pages;

use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class CollectionIndexTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_index_defaults_to_request_driven_hierarchy_mode(): void
    {
        $root = Collection::factory()->create(['internal_name' => 'Root Collection']);
        Collection::factory()->create([
            'internal_name' => 'Hidden Child',
            'parent_id' => $root->id,
            'language_id' => $root->language_id,
            'context_id' => $root->context_id,
        ]);

        $response = $this->get(route('collections.index'));

        $response
            ->assertOk()
            ->assertViewIs('collections.index')
            ->assertSee('Root Collection')
            ->assertDontSee('Hidden Child');

        $this->assertStringNotContainsString('livewire:dynamic-component', $response->getContent());
        $this->assertStringNotContainsString('wire:click', $response->getContent());
    }

    public function test_index_can_search_by_internal_name_in_flat_mode(): void
    {
        Collection::factory()->create(['internal_name' => 'Temple Route']);
        Collection::factory()->create(['internal_name' => 'Other Collection']);

        $response = $this->get(route('collections.index', [
            'q' => 'Temple',
            'mode' => 'flat',
        ]));

        $response
            ->assertOk()
            ->assertSee('Temple Route')
            ->assertDontSee('Other Collection');
    }

    public function test_index_can_drill_down_and_render_hierarchy_breadcrumbs(): void
    {
        $root = Collection::factory()->create(['internal_name' => 'Root Collection']);
        $child = Collection::factory()->create([
            'internal_name' => 'Child Collection',
            'parent_id' => $root->id,
            'language_id' => $root->language_id,
            'context_id' => $root->context_id,
        ]);
        Collection::factory()->create([
            'internal_name' => 'Grandchild Collection',
            'parent_id' => $child->id,
            'language_id' => $root->language_id,
            'context_id' => $root->context_id,
        ]);

        $response = $this->get(route('collections.index', [
            'mode' => 'hierarchy',
            'parent_id' => $root->id,
        ]));

        $response
            ->assertOk()
            ->assertSee('All Collections')
            ->assertSee('Root Collection')
            ->assertSee('Child Collection')
            ->assertDontSee('Grandchild Collection');

        $this->assertSame('Root Collection', $response->viewData('breadcrumbs')[0]['label']);
    }

    public function test_index_can_render_flat_mode_sorted_by_display_order(): void
    {
        Collection::factory()->create([
            'internal_name' => 'Second Collection',
            'display_order' => 2,
        ]);
        Collection::factory()->create([
            'internal_name' => 'First Collection',
            'display_order' => 1,
        ]);

        $response = $this->get(route('collections.index', [
            'mode' => 'flat',
            'sort' => 'display_order',
            'direction' => 'asc',
        ]));

        $response
            ->assertOk()
            ->assertSeeInOrder(['First Collection', 'Second Collection']);
    }

    public function test_index_preserves_query_strings_in_pagination_and_sort_links(): void
    {
        foreach (range(1, 11) as $index) {
            Collection::factory()->create([
                'internal_name' => 'Collection '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            ]);
        }

        $response = $this->get(route('collections.index', [
            'q' => 'Collection',
            'mode' => 'flat',
            'per_page' => 10,
            'sort' => 'internal_name',
            'direction' => 'asc',
        ]));

        $response->assertOk();

        $paginator = $response->viewData('collections');
        $nextPageUrl = $paginator->nextPageUrl();

        $this->assertNotNull($nextPageUrl);
        $this->assertStringContainsString('q=Collection', $nextPageUrl);
        $this->assertStringContainsString('mode=flat', $nextPageUrl);
        $this->assertStringContainsString('per_page=10', $nextPageUrl);
        $this->assertStringContainsString(
            'href="http://localhost/web/collections?mode=flat&amp;q=Collection&amp;per_page=10&amp;sort=created_at&amp;direction=asc&amp;page=1"',
            $response->getContent(),
        );
    }
}

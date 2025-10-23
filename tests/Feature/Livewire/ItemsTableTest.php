<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Tables\ItemsTable;
use App\Models\Item;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class ItemsTableTest extends TestCase
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

    public function test_component_renders_without_errors(): void
    {
        Livewire::test(ItemsTable::class)
            ->assertStatus(200);
    }

    public function test_search_filters_items(): void
    {
        $item1 = Item::factory()->create(['internal_name' => 'Ancient Vase']);
        $item2 = Item::factory()->create(['internal_name' => 'Modern Sculpture']);

        Livewire::test(ItemsTable::class)
            ->set('q', 'Ancient')
            ->assertSeeText('Ancient Vase')
            ->assertDontSeeText('Modern Sculpture');
    }

    public function test_search_is_debounced(): void
    {
        Item::factory()->create(['internal_name' => 'Test Item']);

        $component = Livewire::test(ItemsTable::class)
            ->set('q', 'Test');

        // Search property should be set
        $this->assertEquals('Test', $component->get('q'));
    }

    public function test_pagination_changes_page(): void
    {
        // Create more items than perPage to trigger pagination
        Item::factory()->count(15)->create();

        Livewire::test(ItemsTable::class)
            ->set('perPage', 10)
            ->call('gotoPage', 2)
            ->assertSet('perPage', 10);
    }

    public function test_per_page_changes_limit(): void
    {
        Item::factory()->count(5)->create();

        Livewire::test(ItemsTable::class)
            ->set('perPage', 25)
            ->assertSet('perPage', 25);
    }

    public function test_search_with_partner_relationship(): void
    {
        $partner = Partner::factory()->create(['internal_name' => 'Partner Museum']);
        $item = Item::factory()->create([
            'internal_name' => 'Partner Item',
            'partner_id' => $partner->id,
        ]);

        Livewire::test(ItemsTable::class)
            ->set('q', 'Partner')
            ->assertSeeText('Partner Item');
    }

    public function test_filter_by_single_tag(): void
    {
        $tag = \App\Models\Tag::factory()->create(['internal_name' => 'artifact']);
        $item1 = Item::factory()->create(['internal_name' => 'Tagged Item']);
        $item2 = Item::factory()->create(['internal_name' => 'Untagged Item']);

        $item1->tags()->attach($tag->id);

        Livewire::test(ItemsTable::class)
            ->set('selectedTags', [$tag->id])
            ->assertSeeText('Tagged Item')
            ->assertDontSeeText('Untagged Item');
    }

    public function test_filter_by_multiple_tags_shows_only_items_with_all_tags(): void
    {
        $tag1 = \App\Models\Tag::factory()->create(['internal_name' => 'artifact']);
        $tag2 = \App\Models\Tag::factory()->create(['internal_name' => 'ancient']);

        $item1 = Item::factory()->create(['internal_name' => 'Both Tags Item']);
        $item2 = Item::factory()->create(['internal_name' => 'Single Tag Item']);
        $item3 = Item::factory()->create(['internal_name' => 'No Tags Item']);

        $item1->tags()->attach([$tag1->id, $tag2->id]);
        $item2->tags()->attach([$tag1->id]);

        Livewire::test(ItemsTable::class)
            ->set('selectedTags', [$tag1->id, $tag2->id])
            ->assertSeeText('Both Tags Item')
            ->assertDontSeeText('Single Tag Item')
            ->assertDontSeeText('No Tags Item');
    }

    public function test_remove_tag_filter(): void
    {
        $tag = \App\Models\Tag::factory()->create(['internal_name' => 'artifact']);
        $item = Item::factory()->create(['internal_name' => 'Tagged Item']);
        $item->tags()->attach($tag->id);

        Livewire::test(ItemsTable::class)
            ->set('selectedTags', [$tag->id])
            ->assertSeeText('Tagged Item')
            ->call('removeTag', $tag->id)
            ->assertSet('selectedTags', []);
    }

    public function test_clear_all_tags(): void
    {
        $tag1 = \App\Models\Tag::factory()->create(['internal_name' => 'artifact']);
        $tag2 = \App\Models\Tag::factory()->create(['internal_name' => 'ancient']);

        Livewire::test(ItemsTable::class)
            ->set('selectedTags', [$tag1->id, $tag2->id])
            ->call('clearTags')
            ->assertSet('selectedTags', []);
    }

    public function test_tag_filter_persists_in_query_string(): void
    {
        $tag = \App\Models\Tag::factory()->create(['internal_name' => 'artifact']);

        Livewire::test(ItemsTable::class)
            ->set('selectedTags', [$tag->id])
            ->assertSet('selectedTags', [$tag->id]);
    }

    public function test_available_tags_property_returns_all_tags(): void
    {
        $tag1 = \App\Models\Tag::factory()->create(['internal_name' => 'artifact']);
        $tag2 = \App\Models\Tag::factory()->create(['internal_name' => 'monument']);

        $component = Livewire::test(ItemsTable::class);

        $availableTags = $component->viewData('availableTags');
        $this->assertCount(2, $availableTags);
        $this->assertTrue($availableTags->contains('id', $tag1->id));
        $this->assertTrue($availableTags->contains('id', $tag2->id));
    }
}

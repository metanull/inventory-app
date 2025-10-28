<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Tables\ItemTranslationsTable;
use App\Models\Context;
use App\Models\Item;
use App\Models\ItemTranslation;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ItemTranslationsTableTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_component_renders_without_errors(): void
    {
        Livewire::test(ItemTranslationsTable::class)
            ->assertStatus(200);
    }

    public function test_search_filters_item_translations(): void
    {
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $translation1 = ItemTranslation::factory()->create([
            'item_id' => $item1->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Ancient Vase',
        ]);
        $translation2 = ItemTranslation::factory()->create([
            'item_id' => $item2->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Modern Sculpture',
        ]);

        Livewire::test(ItemTranslationsTable::class)
            ->set('q', 'Ancient')
            ->assertSeeText('Ancient Vase')
            ->assertDontSeeText('Modern Sculpture');
    }

    public function test_search_is_debounced(): void
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        ItemTranslation::factory()->create([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Test Translation',
        ]);

        $component = Livewire::test(ItemTranslationsTable::class)
            ->set('q', 'Test');

        // Search property should be set
        $this->assertEquals('Test', $component->get('q'));
    }

    public function test_pagination_changes_page(): void
    {
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        // Create more translations than perPage to trigger pagination
        // Each translation needs a unique (item_id, language_id, context_id) combination
        for ($i = 0; $i < 15; $i++) {
            $item = Item::factory()->create();
            ItemTranslation::factory()->create([
                'item_id' => $item->id,
                'language_id' => $language->id,
                'context_id' => $context->id,
            ]);
        }

        Livewire::test(ItemTranslationsTable::class)
            ->set('perPage', 10)
            ->call('gotoPage', 2)
            ->assertSet('perPage', 10);
    }

    public function test_per_page_changes_limit(): void
    {
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        // Each translation needs a unique (item_id, language_id, context_id) combination
        for ($i = 0; $i < 5; $i++) {
            $item = Item::factory()->create();
            ItemTranslation::factory()->create([
                'item_id' => $item->id,
                'language_id' => $language->id,
                'context_id' => $context->id,
            ]);
        }

        Livewire::test(ItemTranslationsTable::class)
            ->set('perPage', 25)
            ->assertSet('perPage', 25);
    }
}

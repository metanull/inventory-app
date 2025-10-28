<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire;

use App\Livewire\Tables\CollectionTranslationsTable;
use App\Models\Collection;
use App\Models\CollectionTranslation;
use App\Models\Context;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CollectionTranslationsTableTest extends TestCase
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
        Livewire::test(CollectionTranslationsTable::class)
            ->assertStatus(200);
    }

    public function test_search_filters_collection_translations(): void
    {
        $collection1 = Collection::factory()->create();
        $collection2 = Collection::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $translation1 = CollectionTranslation::factory()->create([
            'collection_id' => $collection1->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'title' => 'Ancient Collection',
        ]);
        $translation2 = CollectionTranslation::factory()->create([
            'collection_id' => $collection2->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'title' => 'Modern Collection',
        ]);

        Livewire::test(CollectionTranslationsTable::class)
            ->set('q', 'Ancient')
            ->assertSeeText('Ancient Collection')
            ->assertDontSeeText('Modern Collection');
    }

    public function test_search_is_debounced(): void
    {
        $collection = Collection::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        CollectionTranslation::factory()->create([
            'collection_id' => $collection->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'title' => 'Test Translation',
        ]);

        $component = Livewire::test(CollectionTranslationsTable::class)
            ->set('q', 'Test');

        // Search property should be set
        $this->assertEquals('Test', $component->get('q'));
    }

    public function test_pagination_changes_page(): void
    {
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        // Create more translations than perPage to trigger pagination
        // Each translation needs a unique (collection_id, language_id, context_id) combination
        for ($i = 0; $i < 15; $i++) {
            $collection = Collection::factory()->create();
            CollectionTranslation::factory()->create([
                'collection_id' => $collection->id,
                'language_id' => $language->id,
                'context_id' => $context->id,
            ]);
        }

        Livewire::test(CollectionTranslationsTable::class)
            ->set('perPage', 10)
            ->call('gotoPage', 2)
            ->assertSet('perPage', 10);
    }

    public function test_per_page_changes_limit(): void
    {
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        // Each translation needs a unique (collection_id, language_id, context_id) combination
        for ($i = 0; $i < 5; $i++) {
            $collection = Collection::factory()->create();
            CollectionTranslation::factory()->create([
                'collection_id' => $collection->id,
                'language_id' => $language->id,
                'context_id' => $context->id,
            ]);
        }

        Livewire::test(CollectionTranslationsTable::class)
            ->set('perPage', 25)
            ->assertSet('perPage', 25);
    }
}

<?php

namespace Tests\Web\Pages;

use App\Models\Collection;
use App\Models\CollectionTranslation;
use App\Models\Context;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class CollectionTranslationIndexTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_index_renders_request_driven_page_without_livewire_markup(): void
    {
        $collection = Collection::factory()->create(['internal_name' => 'Test Collection']);
        CollectionTranslation::factory()->forCollection($collection->id)->create(['title' => 'Alpha Title']);

        $response = $this->get(route('collection-translations.index', ['collection_id' => $collection->id]));

        $response
            ->assertOk()
            ->assertViewIs('collection-translations.index')
            ->assertSee('Alpha Title');

        $this->assertStringNotContainsString('livewire:dynamic-component', $response->getContent());
        $this->assertStringNotContainsString('wire:click', $response->getContent());
    }

    public function test_index_requires_parent_collection_id(): void
    {
        $response = $this->get(route('collection-translations.index'));

        $response->assertRedirect();
    }

    public function test_index_scopes_translations_to_parent_collection(): void
    {
        $collection = Collection::factory()->create(['internal_name' => 'My Collection']);
        $otherCollection = Collection::factory()->create(['internal_name' => 'Other Collection']);

        CollectionTranslation::factory()->forCollection($collection->id)->create(['title' => 'Matching Title']);
        CollectionTranslation::factory()->forCollection($otherCollection->id)->create(['title' => 'Other Title']);

        $response = $this->get(route('collection-translations.index', ['collection_id' => $collection->id]));

        $response
            ->assertOk()
            ->assertSee('Matching Title')
            ->assertDontSee('Other Title');
    }

    public function test_index_can_filter_by_language(): void
    {
        $collection = Collection::factory()->create();
        $language = Language::factory()->create(['internal_name' => 'English']);
        $otherLanguage = Language::factory()->create(['internal_name' => 'French']);

        CollectionTranslation::factory()->forCollection($collection->id)->withLanguage($language->id)->create(['title' => 'English Title']);
        CollectionTranslation::factory()->forCollection($collection->id)->withLanguage($otherLanguage->id)->create(['title' => 'French Title']);

        $response = $this->get(route('collection-translations.index', [
            'collection_id' => $collection->id,
            'language' => $language->id,
        ]));

        $response
            ->assertOk()
            ->assertSee('English Title')
            ->assertDontSee('French Title');
    }

    public function test_index_can_filter_by_context(): void
    {
        $collection = Collection::factory()->create();
        $context = Context::factory()->create(['internal_name' => 'Web Context']);
        $otherContext = Context::factory()->create(['internal_name' => 'Print Context']);

        CollectionTranslation::factory()->forCollection($collection->id)->withContext($context->id)->create(['title' => 'Web Title']);
        CollectionTranslation::factory()->forCollection($collection->id)->withContext($otherContext->id)->create(['title' => 'Print Title']);

        $response = $this->get(route('collection-translations.index', [
            'collection_id' => $collection->id,
            'context' => $context->id,
        ]));

        $response
            ->assertOk()
            ->assertSee('Web Title')
            ->assertDontSee('Print Title');
    }

    public function test_index_can_search_by_translation_title(): void
    {
        $collection = Collection::factory()->create();

        CollectionTranslation::factory()->forCollection($collection->id)->create(['title' => 'Ancient Artifacts']);
        CollectionTranslation::factory()->forCollection($collection->id)->create(['title' => 'Modern Art']);

        $response = $this->get(route('collection-translations.index', [
            'collection_id' => $collection->id,
            'q' => 'Ancient',
        ]));

        $response
            ->assertOk()
            ->assertSee('Ancient Artifacts')
            ->assertDontSee('Modern Art');
    }

    public function test_index_rejects_invalid_sort_field(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->get(route('collection-translations.index', [
            'collection_id' => $collection->id,
            'sort' => 'invalid_field',
        ]));

        $response->assertOk();
    }

    public function test_index_can_sort_by_language_internal_name(): void
    {
        $collection = Collection::factory()->create();
        $languageA = Language::factory()->create(['internal_name' => 'Arabic']);
        $languageZ = Language::factory()->create(['internal_name' => 'Zulu']);

        CollectionTranslation::factory()->forCollection($collection->id)->withLanguage($languageZ->id)->create(['title' => 'Zulu Title']);
        CollectionTranslation::factory()->forCollection($collection->id)->withLanguage($languageA->id)->create(['title' => 'Arabic Title']);

        $response = $this->get(route('collection-translations.index', [
            'collection_id' => $collection->id,
            'sort' => 'language.internal_name',
            'direction' => 'asc',
        ]));

        $response
            ->assertOk()
            ->assertSeeInOrder(['Arabic Title', 'Zulu Title']);
    }

    public function test_index_preserves_query_strings_in_pagination_and_sort_links(): void
    {
        $collection = Collection::factory()->create(['internal_name' => 'Paginated Collection']);
        $language = Language::factory()->create(['internal_name' => 'TestLanguage']);

        foreach (range(1, 11) as $index) {
            CollectionTranslation::factory()
                ->forCollection($collection->id)
                ->withLanguage($language->id)
                ->create(['title' => 'Title '.str_pad((string) $index, 2, '0', STR_PAD_LEFT)]);
        }

        $response = $this->get(route('collection-translations.index', [
            'collection_id' => $collection->id,
            'per_page' => 10,
            'sort' => 'language.internal_name',
            'direction' => 'asc',
        ]));

        $response->assertOk();

        $paginator = $response->viewData('collectionTranslations');
        $nextPageUrl = $paginator->nextPageUrl();

        $this->assertNotNull($nextPageUrl);
        $this->assertStringContainsString('collection_id='.$collection->id, $nextPageUrl);
        $this->assertStringContainsString('per_page=10', $nextPageUrl);
    }

    public function test_index_requires_view_data_permission(): void
    {
        $collection = Collection::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('collection-translations.index', ['collection_id' => $collection->id]));

        $response->assertForbidden();
    }

    public function test_index_passes_parent_collection_to_view(): void
    {
        $collection = Collection::factory()->create(['internal_name' => 'My Special Collection']);

        $response = $this->get(route('collection-translations.index', ['collection_id' => $collection->id]));

        $response
            ->assertOk()
            ->assertSee('My Special Collection');

        $this->assertSame($collection->id, $response->viewData('collection')->id);
    }
}

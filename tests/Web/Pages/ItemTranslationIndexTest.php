<?php

namespace Tests\Web\Pages;

use App\Models\Context;
use App\Models\Item;
use App\Models\ItemTranslation;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class ItemTranslationIndexTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_index_renders_request_driven_page_without_livewire_markup(): void
    {
        $item = Item::factory()->create(['internal_name' => 'Test Item']);
        ItemTranslation::factory()->forItem($item->id)->create(['name' => 'Alpha Translation']);

        $response = $this->get(route('item-translations.index', ['item_id' => $item->id]));

        $response
            ->assertOk()
            ->assertViewIs('item-translations.index')
            ->assertSee('Alpha Translation');

        $this->assertStringNotContainsString('livewire:dynamic-component', $response->getContent());
        $this->assertStringNotContainsString('wire:click', $response->getContent());
    }

    public function test_index_requires_parent_item_id(): void
    {
        $response = $this->get(route('item-translations.index'));

        $response->assertRedirect();
    }

    public function test_index_scopes_translations_to_parent_item(): void
    {
        $item = Item::factory()->create(['internal_name' => 'My Item']);
        $otherItem = Item::factory()->create(['internal_name' => 'Other Item']);

        ItemTranslation::factory()->forItem($item->id)->create(['name' => 'Matching Translation']);
        ItemTranslation::factory()->forItem($otherItem->id)->create(['name' => 'Other Translation']);

        $response = $this->get(route('item-translations.index', ['item_id' => $item->id]));

        $response
            ->assertOk()
            ->assertSee('Matching Translation')
            ->assertDontSee('Other Translation');
    }

    public function test_index_can_filter_by_language(): void
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create(['internal_name' => 'English']);
        $otherLanguage = Language::factory()->create(['internal_name' => 'French']);

        ItemTranslation::factory()->forItem($item->id)->forLanguage($language->id)->create(['name' => 'English Translation']);
        ItemTranslation::factory()->forItem($item->id)->forLanguage($otherLanguage->id)->create(['name' => 'French Translation']);

        $response = $this->get(route('item-translations.index', [
            'item_id' => $item->id,
            'language' => $language->id,
        ]));

        $response
            ->assertOk()
            ->assertSee('English Translation')
            ->assertDontSee('French Translation');
    }

    public function test_index_can_filter_by_context(): void
    {
        $item = Item::factory()->create();
        $context = Context::factory()->create(['internal_name' => 'Web Context']);
        $otherContext = Context::factory()->create(['internal_name' => 'Print Context']);

        ItemTranslation::factory()->forItem($item->id)->forContext($context->id)->create(['name' => 'Web Translation']);
        ItemTranslation::factory()->forItem($item->id)->forContext($otherContext->id)->create(['name' => 'Print Translation']);

        $response = $this->get(route('item-translations.index', [
            'item_id' => $item->id,
            'context' => $context->id,
        ]));

        $response
            ->assertOk()
            ->assertSee('Web Translation')
            ->assertDontSee('Print Translation');
    }

    public function test_index_can_search_by_translation_name(): void
    {
        $item = Item::factory()->create();

        ItemTranslation::factory()->forItem($item->id)->create(['name' => 'Temple Artifact']);
        ItemTranslation::factory()->forItem($item->id)->create(['name' => 'Other Artifact']);

        $response = $this->get(route('item-translations.index', [
            'item_id' => $item->id,
            'q' => 'Temple',
        ]));

        $response
            ->assertOk()
            ->assertSee('Temple Artifact')
            ->assertDontSee('Other Artifact');
    }

    public function test_index_rejects_invalid_sort_field(): void
    {
        $item = Item::factory()->create();

        $response = $this->get(route('item-translations.index', [
            'item_id' => $item->id,
            'sort' => 'invalid_field',
        ]));

        $response->assertOk();
    }

    public function test_index_can_sort_by_language_internal_name(): void
    {
        $item = Item::factory()->create();
        $languageA = Language::factory()->create(['internal_name' => 'Arabic']);
        $languageZ = Language::factory()->create(['internal_name' => 'Zulu']);

        ItemTranslation::factory()->forItem($item->id)->forLanguage($languageZ->id)->create(['name' => 'Zulu Translation']);
        ItemTranslation::factory()->forItem($item->id)->forLanguage($languageA->id)->create(['name' => 'Arabic Translation']);

        $response = $this->get(route('item-translations.index', [
            'item_id' => $item->id,
            'sort' => 'language.internal_name',
            'direction' => 'asc',
        ]));

        $response
            ->assertOk()
            ->assertSeeInOrder(['Arabic Translation', 'Zulu Translation']);
    }

    public function test_index_preserves_query_strings_in_pagination_and_sort_links(): void
    {
        $item = Item::factory()->create(['internal_name' => 'Paginated Item']);
        $language = Language::factory()->create(['internal_name' => 'TestLanguage']);

        foreach (range(1, 11) as $index) {
            ItemTranslation::factory()
                ->forItem($item->id)
                ->forLanguage($language->id)
                ->create(['name' => 'Translation '.str_pad((string) $index, 2, '0', STR_PAD_LEFT)]);
        }

        $response = $this->get(route('item-translations.index', [
            'item_id' => $item->id,
            'per_page' => 10,
            'sort' => 'language.internal_name',
            'direction' => 'asc',
        ]));

        $response->assertOk();

        $paginator = $response->viewData('itemTranslations');
        $nextPageUrl = $paginator->nextPageUrl();

        $this->assertNotNull($nextPageUrl);
        $this->assertStringContainsString('item_id='.$item->id, $nextPageUrl);
        $this->assertStringContainsString('per_page=10', $nextPageUrl);
    }

    public function test_index_requires_view_data_permission(): void
    {
        $item = Item::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('item-translations.index', ['item_id' => $item->id]));

        $response->assertForbidden();
    }

    public function test_index_passes_parent_item_to_view(): void
    {
        $item = Item::factory()->create(['internal_name' => 'My Special Item']);

        $response = $this->get(route('item-translations.index', ['item_id' => $item->id]));

        $response
            ->assertOk()
            ->assertSee('My Special Item');

        $this->assertSame($item->id, $response->viewData('item')->id);
    }
}

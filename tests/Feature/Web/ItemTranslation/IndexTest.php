<?php

declare(strict_types=1);

namespace Tests\Feature\Web\ItemTranslation;

use App\Models\Context;
use App\Models\Item;
use App\Models\ItemTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class IndexTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsDataUser();
    }

    public function test_index_lists_item_translations_with_pagination(): void
    {
        // Create 20 translations with different items to avoid unique constraint violation
        ItemTranslation::factory()->count(20)->create();

        $response = $this->get(route('item-translations.index'));
        $response->assertOk();
        $response->assertSee('Item Translations');
        $response->assertSee('Rows per page');

        $first = ItemTranslation::query()->orderByDesc('created_at')->first();
        $response->assertSee(e($first->name));
    }

    public function test_index_search_filters_results(): void
    {
        // Create 5 translations with different items to avoid unique constraint violation
        ItemTranslation::factory()->count(5)->create();

        $target = ItemTranslation::factory()->create([
            'name' => 'SPECIAL_TRANSLATION_TOKEN',
        ]);

        $response = $this->get(route('item-translations.index', ['q' => 'SPECIAL_TRANSLATION_TOKEN']));
        $response->assertOk();
        $response->assertSee('SPECIAL_TRANSLATION_TOKEN');

        $nonMatch = ItemTranslation::where('id', '!=', $target->id)->first();
        if ($nonMatch) {
            $response->assertDontSee(e($nonMatch->name));
        }
    }

    public function test_index_can_filter_by_context(): void
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();
        $context1 = Context::factory()->create(['internal_name' => 'Context 1']);
        $context2 = Context::factory()->create(['internal_name' => 'Context 2']);

        $translation1 = ItemTranslation::factory()->create([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context1->id,
            'name' => 'Translation in Context 1',
        ]);

        $translation2 = ItemTranslation::factory()->create([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context2->id,
            'name' => 'Translation in Context 2',
        ]);

        $response = $this->get(route('item-translations.index', ['contextFilter' => $context1->id]));
        $response->assertOk();
        $response->assertSee('Translation in Context 1');
        $response->assertDontSee('Translation in Context 2');
    }
}

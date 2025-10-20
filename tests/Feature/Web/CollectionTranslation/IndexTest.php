<?php

declare(strict_types=1);

namespace Tests\Feature\Web\CollectionTranslation;

use App\Models\Collection;
use App\Models\CollectionTranslation;
use App\Models\Context;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class IndexTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_index_lists_collection_translations_with_pagination(): void
    {
        // Create 20 translations with different collections to avoid unique constraint violation
        CollectionTranslation::factory()->count(20)->create();

        $response = $this->get(route('collection-translations.index'));
        $response->assertOk();
        $response->assertSee('Collection Translations');
        $response->assertSee('Rows per page');

        $first = CollectionTranslation::query()->orderByDesc('created_at')->first();
        $response->assertSee(e($first->title));
    }

    public function test_index_search_filters_results(): void
    {
        // Create 5 translations with different collections to avoid unique constraint violation
        CollectionTranslation::factory()->count(5)->create();

        $target = CollectionTranslation::factory()->create([
            'title' => 'SPECIAL_COLLECTION_TRANSLATION_TOKEN',
        ]);

        $response = $this->get(route('collection-translations.index', ['q' => 'SPECIAL_COLLECTION_TRANSLATION_TOKEN']));
        $response->assertOk();
        $response->assertSee('SPECIAL_COLLECTION_TRANSLATION_TOKEN');

        $nonMatch = CollectionTranslation::where('id', '!=', $target->id)->first();
        if ($nonMatch) {
            $response->assertDontSee(e($nonMatch->title));
        }
    }

    public function test_index_can_filter_by_context(): void
    {
        $collection = Collection::factory()->create();
        $language = Language::factory()->create();
        $context1 = Context::factory()->create(['internal_name' => 'Context 1']);
        $context2 = Context::factory()->create(['internal_name' => 'Context 2']);

        $translation1 = CollectionTranslation::factory()->create([
            'collection_id' => $collection->id,
            'language_id' => $language->id,
            'context_id' => $context1->id,
            'title' => 'Translation in Context 1',
        ]);

        $translation2 = CollectionTranslation::factory()->create([
            'collection_id' => $collection->id,
            'language_id' => $language->id,
            'context_id' => $context2->id,
            'title' => 'Translation in Context 2',
        ]);

        $response = $this->get(route('collection-translations.index', ['contextFilter' => $context1->id]));
        $response->assertOk();
        $response->assertSee('Translation in Context 1');
        $response->assertDontSee('Translation in Context 2');
    }
}

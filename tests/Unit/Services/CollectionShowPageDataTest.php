<?php

namespace Tests\Unit\Services;

use App\Models\Collection;
use App\Models\CollectionImage;
use App\Models\CollectionTranslation;
use App\Models\Context;
use App\Models\Item;
use App\Models\Language;
use App\Services\Web\CollectionShowPageData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CollectionShowPageDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_build_preloads_all_relations_used_by_collection_show_page(): void
    {
        $defaultLanguage = Language::factory()->withIsDefault()->create(['id' => 'eng']);
        $defaultContext = Context::factory()->withIsDefault()->create();
        $collection = Collection::factory()->create([
            'language_id' => $defaultLanguage->id,
            'context_id' => $defaultContext->id,
        ]);
        Collection::factory()->create([
            'parent_id' => $collection->id,
            'language_id' => $collection->language_id,
            'context_id' => $collection->context_id,
        ]);
        $item = Item::factory()->create();
        $collection->attachItem($item);
        CollectionImage::factory()->create(['collection_id' => $collection->id]);
        CollectionTranslation::factory()->forCollection($collection->id)->withLanguage($defaultLanguage->id)->withContext($defaultContext->id)->create();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $collection = $collection->fresh();
        $pageData = app(CollectionShowPageData::class)->build($collection);
        $queryCountAfterBuild = count(DB::getQueryLog());

        $collection->context?->internal_name;
        $collection->language?->internal_name;
        $collection->parent?->internal_name;
        $collection->children->first()?->internal_name;
        $collection->attachedItems->first()?->itemImages->first()?->alt_text;
        $this->assertArrayHasKey('sections', $pageData);
        $this->assertSame(
            ['images', 'children', 'items', 'translations', 'parent', 'system'],
            array_keys($pageData['sections'])
        );
        $this->assertArrayHasKey('items', $pageData['sections']['items']);
        $this->assertArrayNotHasKey('attachableItems', $pageData['sections']['items']);
        $this->assertArrayHasKey('collection', $pageData['sections']['parent']);
        $this->assertArrayNotHasKey('options', $pageData['sections']['parent']);

        $pageData['sections']['images']['images']->first()?->alt_text;
        $pageData['sections']['translations']['groups']->first()['translations']->first()?->language?->internal_name;
        $pageData['sections']['children']['items']->first()?->internal_name;
        $pageData['sections']['items']['items']->first()?->internal_name;
        $pageData['sections']['parent']['collection']?->internal_name;
        $pageData['sections']['children']['items']->first()?->display_order;
        $pageData['sections']['system']['id'];

        $this->assertCount($queryCountAfterBuild, DB::getQueryLog());
        $this->assertTrue($collection->relationLoaded('context'));
        $this->assertTrue($collection->relationLoaded('language'));
        $this->assertTrue($collection->relationLoaded('parent'));
        $this->assertTrue($collection->relationLoaded('children'));
        $this->assertTrue($collection->relationLoaded('attachedItems'));

        DB::disableQueryLog();
    }
}

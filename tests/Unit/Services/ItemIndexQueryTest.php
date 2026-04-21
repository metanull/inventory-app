<?php

namespace Tests\Unit\Services;

use App\Models\Collection;
use App\Models\Country;
use App\Models\Item;
use App\Models\Partner;
use App\Services\Web\ItemIndexQuery;
use App\Support\Web\Lists\ListState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ItemIndexQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_paginate_eager_loads_rendered_relations_without_extra_queries(): void
    {
        $partner = Partner::factory()->create(['internal_name' => 'Partner']);
        $collection = Collection::factory()->create(['internal_name' => 'Collection']);
        $country = Country::factory()->create(['internal_name' => 'Country']);

        $items = Item::factory()->count(3)->create([
            'partner_id' => $partner->id,
            'collection_id' => $collection->id,
            'country_id' => $country->id,
        ]);

        Item::factory()->count(2)->create(['parent_id' => $items->first()->id]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $paginator = app(ItemIndexQuery::class)->paginate(new ListState(
            search: null,
            sort: 'internal_name',
            direction: 'asc',
            page: 1,
            perPage: 10,
            filters: ['hierarchy' => false],
        ));

        foreach ($paginator->items() as $item) {
            $item->partner?->internal_name;
            $item->collection?->internal_name;
            $item->country?->internal_name;
            $item->children_count;
        }

        $queries = DB::getQueryLog();

        DB::disableQueryLog();

        $this->assertCount(5, $queries);

        foreach ($paginator->items() as $item) {
            $this->assertTrue($item->relationLoaded('partner'));
            $this->assertTrue($item->relationLoaded('collection'));
            $this->assertTrue($item->relationLoaded('country'));
            $this->assertFalse($item->relationLoaded('project'));
            $this->assertNotNull($item->children_count);
        }
    }
}

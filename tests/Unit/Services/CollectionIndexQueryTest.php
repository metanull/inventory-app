<?php

namespace Tests\Unit\Services;

use App\Models\Collection;
use App\Services\Web\CollectionIndexQuery;
use App\Support\Web\Lists\CollectionListDefinition;
use App\Support\Web\Lists\ListState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CollectionIndexQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_paginate_eager_loads_rendered_relations_and_children_count_without_extra_queries(): void
    {
        $collections = Collection::factory()->count(3)->create();

        Collection::factory()->count(2)->create([
            'parent_id' => $collections->first()->id,
            'language_id' => $collections->first()->language_id,
            'context_id' => $collections->first()->context_id,
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $paginator = app(CollectionIndexQuery::class)->paginate(new ListState(
            search: null,
            sort: 'internal_name',
            direction: 'asc',
            page: 1,
            perPage: 10,
            filters: ['mode' => CollectionListDefinition::MODE_FLAT],
        ));

        foreach ($paginator->items() as $collection) {
            $collection->context?->internal_name;
            $collection->language?->internal_name;
            $collection->children_count;
        }

        $queries = DB::getQueryLog();

        DB::disableQueryLog();

        $this->assertCount(4, $queries);

        foreach ($paginator->items() as $collection) {
            $this->assertTrue($collection->relationLoaded('context'));
            $this->assertTrue($collection->relationLoaded('language'));
            $this->assertNotNull($collection->children_count);
        }
    }
}

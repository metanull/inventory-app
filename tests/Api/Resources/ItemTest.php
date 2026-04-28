<?php

namespace Tests\Api\Resources;

use App\Models\Item;
use App\Models\ItemTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Api\Traits\AuthenticatesApiRequests;
use Tests\Api\Traits\TestsApiCrud;
use Tests\Api\Traits\TestsApiTagManagement;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use AuthenticatesApiRequests;
    use RefreshDatabase;
    use TestsApiCrud;
    use TestsApiTagManagement;

    protected function getResourceName(): string
    {
        return 'item';
    }

    protected function getModelClass(): string
    {
        return Item::class;
    }

    public function test_show_with_include_translations_returns_200_without_infinite_recursion(): void
    {
        $item = Item::factory()->create();
        ItemTranslation::factory()->create(['item_id' => $item->id]);

        $response = $this->getJson(route('item.show', $item).'?include=translations');

        $response->assertOk()
            ->assertJsonPath('data.id', $item->id)
            ->assertJsonStructure(['data' => ['translations']]);
    }
}

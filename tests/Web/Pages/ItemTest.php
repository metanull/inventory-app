<?php

namespace Tests\Web\Pages;

use App\Models\Item;
use App\Models\ItemImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebCrud;

class ItemTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebCrud;

    protected function getRouteName(): string
    {
        return 'items';
    }

    protected function getModelClass(): string
    {
        return Item::class;
    }

    protected function getFormData(): array
    {
        return Item::factory()->make()->toArray();
    }

    public function test_show_renders_child_pictures_in_a_dedicated_section(): void
    {
        $item = Item::factory()->create(['internal_name' => 'Parent Item']);
        $pictureChild = Item::factory()->Picture()->create([
            'parent_id' => $item->id,
            'internal_name' => 'Picture Child',
        ]);
        $pictureImage = ItemImage::factory()->create(['item_id' => $pictureChild->id]);

        $response = $this->get(route('items.show', $item));

        $response->assertOk();
        $response->assertSeeInOrder(['Images', 'Pictures', 'Translations']);
        $response->assertSee('Picture Child');
        $response->assertSee(route('items.item-images.view', [$pictureChild, $pictureImage]), false);
    }
}

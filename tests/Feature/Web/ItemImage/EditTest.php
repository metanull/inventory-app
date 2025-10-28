<?php

namespace Tests\Feature\Web\ItemImage;

use App\Models\Item;
use App\Models\ItemImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class EditTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected Item $item;

    protected ItemImage $itemImage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->item = Item::factory()->create();
        $this->itemImage = ItemImage::factory()->for($this->item)->create();
    }

    public function test_authenticated_user_can_view_edit_form(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->get(route('items.item-images.edit', [$this->item, $this->itemImage]));

        $response->assertOk();
        $response->assertViewIs('item-images.edit');
        $response->assertViewHas('item', $this->item);
        $response->assertViewHas('itemImage', $this->itemImage);
        $response->assertSee('Edit Image Alt Text');
    }

    public function test_edit_form_displays_current_alt_text(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);
        $this->itemImage->update(['alt_text' => 'Test Alt Text']);

        $response = $this->actingAs($user)
            ->get(route('items.item-images.edit', [$this->item, $this->itemImage]));

        $response->assertOk();
        $response->assertSee('Test Alt Text');
    }

    public function test_edit_form_displays_display_order(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->get(route('items.item-images.edit', [$this->item, $this->itemImage]));

        $response->assertOk();
        $response->assertSee('Display Order');
        $response->assertSee((string) $this->itemImage->display_order);
    }

    public function test_cannot_edit_image_from_different_item(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);
        $otherItem = Item::factory()->create();
        $otherItemImage = ItemImage::factory()->for($otherItem)->create();

        $response = $this->actingAs($user)
            ->get(route('items.item-images.edit', [$this->item, $otherItemImage]));

        $response->assertNotFound();
    }

    public function test_guest_cannot_view_edit_form(): void
    {
        $response = $this->get(route('items.item-images.edit', [$this->item, $this->itemImage]));

        $response->assertRedirect(route('login'));
    }
}

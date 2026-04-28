<?php

namespace Tests\Filament\Widgets;

use App\Enums\Permission;
use App\Filament\Widgets\MissingImagesWidget;
use App\Models\Collection;
use App\Models\CollectionImage;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\Partner;
use App\Models\PartnerImage;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MissingImagesWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_widget_is_visible_for_user_with_view_data_permission(): void
    {
        $user = $this->createViewUser();
        $this->actingAs($user);

        $this->assertTrue(MissingImagesWidget::canView());
    }

    public function test_widget_is_not_visible_without_view_data_permission(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([Permission::ACCESS_ADMIN_PANEL->value]);
        $this->actingAs($user);

        $this->assertFalse(MissingImagesWidget::canView());
    }

    public function test_widget_shows_items_without_images(): void
    {
        $user = $this->createViewUser();

        $itemMissing = Item::factory()->Object()->create(['internal_name' => 'No image item']);
        $itemWithImage = Item::factory()->Object()->create(['internal_name' => 'Has image item']);
        ItemImage::factory()->forItem($itemWithImage)->create();

        Livewire::actingAs($user)
            ->test(MissingImagesWidget::class, ['ownerType' => 'item'])
            ->assertCanSeeTableRecords([$itemMissing])
            ->assertCanNotSeeTableRecords([$itemWithImage]);
    }

    public function test_widget_shows_collections_without_images(): void
    {
        $user = $this->createViewUser();

        $colMissing = Collection::factory()->create(['internal_name' => 'No image collection']);
        $colWithImage = Collection::factory()->create(['internal_name' => 'Has image collection']);
        CollectionImage::factory()->forCollection($colWithImage)->create();

        Livewire::actingAs($user)
            ->test(MissingImagesWidget::class, ['ownerType' => 'collection'])
            ->assertCanSeeTableRecords([$colMissing])
            ->assertCanNotSeeTableRecords([$colWithImage]);
    }

    public function test_widget_shows_partners_without_images(): void
    {
        $user = $this->createViewUser();

        $partnerMissing = Partner::factory()->create(['internal_name' => 'No image partner']);
        $partnerWithImage = Partner::factory()->create(['internal_name' => 'Has image partner']);
        PartnerImage::factory()->forPartner($partnerWithImage)->create();

        Livewire::actingAs($user)
            ->test(MissingImagesWidget::class, ['ownerType' => 'partner'])
            ->assertCanSeeTableRecords([$partnerMissing])
            ->assertCanNotSeeTableRecords([$partnerWithImage]);
    }

    public function test_widget_shows_empty_state_when_all_items_have_images(): void
    {
        $user = $this->createViewUser();
        $item = Item::factory()->Object()->create();
        ItemImage::factory()->forItem($item)->create();

        Livewire::actingAs($user)
            ->test(MissingImagesWidget::class, ['ownerType' => 'item'])
            ->assertSee('All items have images');
    }

    public function test_widget_defaults_to_item_type(): void
    {
        $user = $this->createViewUser();

        Livewire::actingAs($user)
            ->test(MissingImagesWidget::class)
            ->assertSee('Items Without Images');
    }

    public function test_switching_to_collection_type_changes_heading(): void
    {
        $user = $this->createViewUser();

        Livewire::actingAs($user)
            ->test(MissingImagesWidget::class, ['ownerType' => 'collection'])
            ->assertSee('Collections Without Images');
    }

    public function test_switching_to_partner_type_changes_heading(): void
    {
        $user = $this->createViewUser();

        Livewire::actingAs($user)
            ->test(MissingImagesWidget::class, ['ownerType' => 'partner'])
            ->assertSee('Partners Without Images');
    }

    protected function createViewUser(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::VIEW_DATA->value,
        ]);

        return $user;
    }
}

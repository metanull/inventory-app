<?php

namespace Tests\Filament\Widgets;

use App\Enums\Permission;
use App\Filament\Widgets\RecentItemsWidget;
use App\Models\Collection;
use App\Models\Item;
use App\Models\Partner;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RecentItemsWidgetTest extends TestCase
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

        $this->assertTrue(RecentItemsWidget::canView());
    }

    public function test_widget_is_not_visible_without_view_data_permission(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([Permission::ACCESS_ADMIN_PANEL->value]);
        $this->actingAs($user);

        $this->assertFalse(RecentItemsWidget::canView());
    }

    public function test_widget_renders_item_table_by_default(): void
    {
        $user = $this->createViewUser();
        Item::factory()->Object()->create(['internal_name' => 'Test Item']);

        Livewire::actingAs($user)
            ->test(RecentItemsWidget::class)
            ->assertSuccessful()
            ->assertSee('Recently Edited Items');
    }

    public function test_widget_shows_recently_edited_items(): void
    {
        $user = $this->createViewUser();
        $item = Item::factory()->Object()->create(['internal_name' => 'Edited Item']);

        Livewire::actingAs($user)
            ->test(RecentItemsWidget::class, ['ownerType' => 'item'])
            ->assertCanSeeTableRecords([$item]);
    }

    public function test_widget_shows_recently_edited_collections(): void
    {
        $user = $this->createViewUser();
        $collection = Collection::factory()->create(['internal_name' => 'Edited Collection']);

        Livewire::actingAs($user)
            ->test(RecentItemsWidget::class, ['ownerType' => 'collection'])
            ->assertCanSeeTableRecords([$collection])
            ->assertSee('Recently Edited Collections');
    }

    public function test_widget_shows_recently_edited_partners(): void
    {
        $user = $this->createViewUser();
        $partner = Partner::factory()->create(['internal_name' => 'Edited Partner']);

        Livewire::actingAs($user)
            ->test(RecentItemsWidget::class, ['ownerType' => 'partner'])
            ->assertCanSeeTableRecords([$partner])
            ->assertSee('Recently Edited Partners');
    }

    public function test_widget_limits_to_ten_items(): void
    {
        $user = $this->createViewUser();
        Item::factory()->Object()->count(15)->create();

        $component = Livewire::actingAs($user)
            ->test(RecentItemsWidget::class, ['ownerType' => 'item']);

        $component->assertSuccessful();

        $this->assertLessThanOrEqual(10, $component->instance()->getTableRecords()->count());
    }

    public function test_widget_shows_updated_at_column_for_items(): void
    {
        $user = $this->createViewUser();
        Item::factory()->Object()->create(['internal_name' => 'Test Item']);

        Livewire::actingAs($user)
            ->test(RecentItemsWidget::class, ['ownerType' => 'item'])
            ->assertSee('Updated');
    }

    public function test_switching_to_collection_type_changes_heading(): void
    {
        $user = $this->createViewUser();

        Livewire::actingAs($user)
            ->test(RecentItemsWidget::class, ['ownerType' => 'collection'])
            ->assertSee('Recently Edited Collections');
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

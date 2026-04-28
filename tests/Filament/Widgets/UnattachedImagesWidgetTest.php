<?php

namespace Tests\Filament\Widgets;

use App\Enums\Permission;
use App\Filament\Widgets\UnattachedImagesWidget;
use App\Models\AvailableImage;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UnattachedImagesWidgetTest extends TestCase
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

        $this->assertTrue(UnattachedImagesWidget::canView());
    }

    public function test_widget_is_not_visible_without_view_data_permission(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([Permission::ACCESS_ADMIN_PANEL->value]);
        $this->actingAs($user);

        $this->assertFalse(UnattachedImagesWidget::canView());
    }

    public function test_widget_renders_successfully(): void
    {
        $user = $this->createViewUser();

        Livewire::actingAs($user)
            ->test(UnattachedImagesWidget::class)
            ->assertSuccessful();
    }

    public function test_widget_shows_available_images(): void
    {
        $user = $this->createViewUser();
        $image = AvailableImage::factory()->create([
            'original_name' => 'test-image.jpg',
            'comment' => 'A test image',
        ]);

        Livewire::actingAs($user)
            ->test(UnattachedImagesWidget::class)
            ->assertCanSeeTableRecords([$image]);
    }

    public function test_widget_shows_empty_state_when_no_images(): void
    {
        $user = $this->createViewUser();

        Livewire::actingAs($user)
            ->test(UnattachedImagesWidget::class)
            ->assertSee('No unattached images');
    }

    public function test_widget_limits_to_ten_images(): void
    {
        $user = $this->createViewUser();
        AvailableImage::factory()->count(15)->create();

        $component = Livewire::actingAs($user)
            ->test(UnattachedImagesWidget::class);

        $component->assertSuccessful();

        $this->assertLessThanOrEqual(10, $component->instance()->getTableRecords()->count());
    }

    public function test_widget_heading_is_correct(): void
    {
        $user = $this->createViewUser();

        Livewire::actingAs($user)
            ->test(UnattachedImagesWidget::class)
            ->assertSee('Available Images (Not Yet Attached)');
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

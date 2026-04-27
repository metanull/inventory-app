<?php

namespace Tests\Filament\Widgets;

use App\Enums\Permission;
use App\Filament\Widgets\StorageUsageWidget;
use App\Models\AvailableImage;
use App\Models\CollectionImage;
use App\Models\ContributorImage;
use App\Models\ImageUpload;
use App\Models\ItemImage;
use App\Models\PartnerImage;
use App\Models\PartnerLogo;
use App\Models\PartnerTranslationImage;
use App\Models\TimelineEventImage;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StorageUsageWidgetTest extends TestCase
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

        $this->assertTrue(
            auth()->loginUsingId($user->id)?->hasPermissionTo(Permission::VIEW_DATA->value)
        );

        $this->actingAs($user);

        $this->assertTrue(StorageUsageWidget::canView());
    }

    public function test_widget_is_not_visible_for_user_without_view_data_permission(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([Permission::ACCESS_ADMIN_PANEL->value]);

        $this->actingAs($user);

        $this->assertFalse(StorageUsageWidget::canView());
    }

    public function test_widget_renders_successfully_for_authorized_user(): void
    {
        $user = $this->createViewUser();

        Livewire::actingAs($user)
            ->test(StorageUsageWidget::class)
            ->assertSuccessful();
    }

    public function test_format_bytes_returns_bytes_label_for_small_values(): void
    {
        $this->assertSame('0 B', StorageUsageWidget::formatBytes(0));
        $this->assertSame('512 B', StorageUsageWidget::formatBytes(512));
        $this->assertSame('1023 B', StorageUsageWidget::formatBytes(1023));
    }

    public function test_format_bytes_returns_kb_label(): void
    {
        $this->assertSame('1.00 KB', StorageUsageWidget::formatBytes(1024));
        $this->assertSame('1.50 KB', StorageUsageWidget::formatBytes(1536));
    }

    public function test_format_bytes_returns_mb_label(): void
    {
        $this->assertSame('1.00 MB', StorageUsageWidget::formatBytes(1_048_576));
        $this->assertSame('2.50 MB', StorageUsageWidget::formatBytes(2_621_440));
    }

    public function test_format_bytes_returns_gb_label(): void
    {
        $this->assertSame('1.00 GB', StorageUsageWidget::formatBytes(1_073_741_824));
    }

    public function test_managed_storage_bytes_sums_all_attached_image_tables(): void
    {
        $user = $this->createViewUser();
        $this->actingAs($user);

        ItemImage::factory()->create(['size' => 1000]);
        CollectionImage::factory()->create(['size' => 2000]);
        PartnerImage::factory()->create(['size' => 3000]);
        PartnerLogo::factory()->create(['size' => 4000]);
        PartnerTranslationImage::factory()->create(['size' => 5000]);
        ContributorImage::factory()->create(['size' => 6000]);
        TimelineEventImage::factory()->create(['size' => 7000]);

        $widget = new StorageUsageWidget;

        $this->assertSame(28_000, $widget->getManagedStorageBytes());
    }

    public function test_available_pool_bytes_sums_available_images(): void
    {
        $user = $this->createViewUser();
        $this->actingAs($user);

        AvailableImage::factory()->create(['size' => 5000]);
        AvailableImage::factory()->create(['size' => 3000]);

        $widget = new StorageUsageWidget;

        $this->assertSame(8_000, $widget->getAvailablePoolBytes());
    }

    public function test_pending_upload_bytes_sums_image_uploads(): void
    {
        $user = $this->createViewUser();
        $this->actingAs($user);

        ImageUpload::factory()->create(['size' => 2000]);
        ImageUpload::factory()->create(['size' => 1500]);

        $widget = new StorageUsageWidget;

        $this->assertSame(3_500, $widget->getPendingUploadBytes());
    }

    public function test_empty_tables_contribute_zero_without_errors(): void
    {
        $user = $this->createViewUser();
        $this->actingAs($user);

        $widget = new StorageUsageWidget;

        $this->assertSame(0, $widget->getManagedStorageBytes());
        $this->assertSame(0, $widget->getAvailablePoolBytes());
        $this->assertSame(0, $widget->getPendingUploadBytes());
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

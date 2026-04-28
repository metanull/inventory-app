<?php

namespace Tests\Filament\Authorization;

use App\Enums\Permission;
use App\Models\AvailableImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AvailableImageResourceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_without_view_data_permission_cannot_see_available_image_navigation_or_pages(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $dashboard = $this->actingAs($user)->get('/admin');

        $dashboard
            ->assertOk()
            ->assertDontSee('Available Images');

        $this->actingAs($user)->get('/admin/available-images')
            ->assertForbidden();
    }

    public function test_users_with_view_data_permission_can_access_available_image_index(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::VIEW_DATA->value,
        ]);

        $availableImage = AvailableImage::factory()->create(['comment' => 'A test image']);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertSee('Available Images');

        $this->actingAs($user)->get('/admin/available-images')
            ->assertOk()
            ->assertSee('A test image');
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Web\AvailableImage;

use App\Models\AvailableImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_index(): void
    {
        $response = $this->get(route('available-images.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_access_show(): void
    {
        $image = AvailableImage::factory()->create();
        $response = $this->get(route('available-images.show', $image));
        $response->assertRedirect(route('login'));
    }

    public function test_user_without_view_permission_cannot_access_index(): void
    {
        $user = \App\Models\User::factory()->create();
        $response = $this->actingAs($user)->get(route('available-images.index'));
        $response->assertForbidden();
    }

    public function test_user_without_view_permission_cannot_access_show(): void
    {
        $user = \App\Models\User::factory()->create();
        $image = AvailableImage::factory()->create();
        $response = $this->actingAs($user)->get(route('available-images.show', $image));
        $response->assertForbidden();
    }

    public function test_user_with_view_permission_can_access_index(): void
    {
        $user = \App\Models\User::factory()->create();
        $user->givePermissionTo(\App\Enums\Permission::VIEW_DATA->value);
        $response = $this->actingAs($user)->get(route('available-images.index'));
        $response->assertOk();
    }

    public function test_user_with_view_permission_can_access_show(): void
    {
        $user = \App\Models\User::factory()->create();
        $user->givePermissionTo(\App\Enums\Permission::VIEW_DATA->value);
        $image = AvailableImage::factory()->create();
        $response = $this->actingAs($user)->get(route('available-images.show', $image));
        $response->assertOk();
    }
}

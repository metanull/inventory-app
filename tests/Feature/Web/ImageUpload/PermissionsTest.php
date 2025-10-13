<?php

declare(strict_types=1);

namespace Tests\Feature\Web\ImageUpload;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake(config('localstorage.uploads.images.disk'));
    }

    public function test_guest_cannot_access_upload_form(): void
    {
        $response = $this->get(route('images.upload'));
        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_upload_image(): void
    {
        $file = UploadedFile::fake()->image('test.jpg');
        $response = $this->post(route('images.store'), ['file' => $file]);
        $response->assertRedirect(route('login'));
    }

    public function test_user_without_create_permission_cannot_access_upload_form(): void
    {
        $user = \App\Models\User::factory()->create();
        $user->givePermissionTo(\App\Enums\Permission::VIEW_DATA->value);
        $response = $this->actingAs($user)->get(route('images.upload'));
        $response->assertForbidden();
    }

    public function test_user_without_create_permission_cannot_upload_image(): void
    {
        $user = \App\Models\User::factory()->create();
        $user->givePermissionTo(\App\Enums\Permission::VIEW_DATA->value);
        $file = UploadedFile::fake()->image('test.jpg');
        $response = $this->actingAs($user)->post(route('images.store'), ['file' => $file]);
        $response->assertForbidden();
    }

    public function test_user_with_create_permission_can_access_upload_form(): void
    {
        $user = \App\Models\User::factory()->create();
        $user->givePermissionTo(\App\Enums\Permission::CREATE_DATA->value);
        $response = $this->actingAs($user)->get(route('images.upload'));
        $response->assertOk();
    }

    public function test_user_with_create_permission_can_upload_image(): void
    {
        $user = \App\Models\User::factory()->create();
        $user->givePermissionTo(\App\Enums\Permission::CREATE_DATA->value);
        $file = UploadedFile::fake()->image('test.jpg');
        $response = $this->actingAs($user)->post(route('images.store'), ['file' => $file]);
        $response->assertRedirect(route('images.upload'));
    }
}

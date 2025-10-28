<?php

namespace Tests\Feature\Api\ImageUpload;

use App\Enums\Permission;
use App\Models\ImageUpload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DestroyTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith(Permission::dataOperations());
        $this->actingAs($this->user);

        Storage::fake('local');
        Event::fake();
        Http::fake();
    }

    public function test_destroy_allows_authenticated_users(): void
    {
        $imageUpload = ImageUpload::factory()->create();

        $response = $this->deleteJson(route('image-upload.destroy', $imageUpload->id));
        $response->assertNoContent();
    }
}

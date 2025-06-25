<?php

namespace Tests\Feature\Api\ImageUpload;

use App\Models\ImageUpload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        Storage::fake('local');
        Event::fake();
    }

    public function test_destroy_allows_authenticated_users(): void
    {
        $imageUpload = ImageUpload::factory()->create();

        $response = $this->deleteJson(route('image-upload.destroy', $imageUpload->id));
        $response->assertNoContent();
    }
}

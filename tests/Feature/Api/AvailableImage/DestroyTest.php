<?php

namespace Tests\Feature\Api\AvailableImage;

use App\Models\AvailableImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
        Event::fake();
        Http::fake();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_destroy_allows_authenticated_users(): void
    {
        $availableImage = AvailableImage::factory()->create();

        $response = $this->deleteJson(route('available-image.destroy', $availableImage->id));
        $response->assertNoContent();
    }

    public function test_destroy_returns_not_found_response_when_not_found(): void
    {
        $response = $this->deleteJson(route('available-image.destroy', 'non-existent-id'));
        $response->assertNotFound();
    }

    public function test_destroy_deletes_a_row(): void
    {
        $availableImage = AvailableImage::factory()->create();

        $this->deleteJson(route('available-image.destroy', $availableImage->id));
        $this->assertDatabaseMissing('available_images', [
            'id' => $availableImage->id,
        ]);
    }

    public function test_destroy_returns_no_content_on_success(): void
    {
        $availableImage = AvailableImage::factory()->create();

        $response = $this->deleteJson(route('available-image.destroy', $availableImage->id));
        $response->assertNoContent();
    }
}

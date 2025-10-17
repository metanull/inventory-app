<?php

namespace Tests\Feature\Api\CollectionImage;

use App\Models\Collection;
use App\Models\CollectionImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_index_forbids_anonymous_access(): void
    {
        $collection = Collection::factory()->create();
        $response = $this->getJson(route('collection.images.index', $collection->id));
        $response->assertUnauthorized();
    }

    public function test_show_forbids_anonymous_access(): void
    {
        $collectionImage = CollectionImage::factory()->create();
        $response = $this->getJson(route('collection-image.show', $collectionImage->id));
        $response->assertUnauthorized();
    }

    public function test_store_forbids_anonymous_access(): void
    {
        $collection = Collection::factory()->create();
        $response = $this->postJson(route('collection.images.store', $collection->id), [
            'path' => 'https://example.com/image.jpg',
            'original_name' => 'test-image.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 150000,
            'alt_text' => 'Test image',
        ]);
        $response->assertUnauthorized();
    }

    public function test_update_forbids_anonymous_access(): void
    {
        $collectionImage = CollectionImage::factory()->create();
        $response = $this->patchJson(route('collection-image.update', $collectionImage->id), [
            'alt_text' => 'Updated alt text',
        ]);
        $response->assertUnauthorized();
    }

    public function test_destroy_forbids_anonymous_access(): void
    {
        $collectionImage = CollectionImage::factory()->create();
        $response = $this->deleteJson(route('collection-image.destroy', $collectionImage->id));
        $response->assertUnauthorized();
    }

    public function test_move_up_forbids_anonymous_access(): void
    {
        $collectionImage = CollectionImage::factory()->create();
        $response = $this->patchJson(route('collection-image.moveUp', $collectionImage->id));
        $response->assertUnauthorized();
    }

    public function test_move_down_forbids_anonymous_access(): void
    {
        $collectionImage = CollectionImage::factory()->create();
        $response = $this->patchJson(route('collection-image.moveDown', $collectionImage->id));
        $response->assertUnauthorized();
    }

    public function test_attach_from_available_forbids_anonymous_access(): void
    {
        $collection = Collection::factory()->create();
        $response = $this->postJson(route('collection.attachImage', $collection->id), [
            'available_image_id' => 'test-uuid',
            'alt_text' => 'Test alt text',
        ]);
        $response->assertUnauthorized();
    }

    public function test_detach_to_available_forbids_anonymous_access(): void
    {
        $collectionImage = CollectionImage::factory()->create();
        $response = $this->postJson(route('collection-image.detach', $collectionImage->id));
        $response->assertUnauthorized();
    }
}

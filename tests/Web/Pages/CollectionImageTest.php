<?php

namespace Tests\Web\Pages;

use App\Models\AvailableImage;
use App\Models\Collection;
use App\Models\CollectionImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class CollectionImageTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_create_requires_authentication(): void
    {
        auth()->logout();
        $collection = Collection::factory()->create();

        $response = $this->get(route('collections.collection-images.create', ['collection' => $collection]));

        $response->assertRedirect(route('login'));
    }

    public function test_create_page_passes_available_images_from_controller(): void
    {
        $collection = Collection::factory()->create();
        AvailableImage::factory()->count(3)->create();

        $response = $this->get(route('collections.collection-images.create', ['collection' => $collection]));

        $response->assertOk()
            ->assertViewIs('collection-images.create')
            ->assertViewHas('availableImages')
            ->assertViewHas('collection');
    }

    public function test_edit_requires_authentication(): void
    {
        auth()->logout();
        $collection = Collection::factory()->create();
        $collectionImage = CollectionImage::factory()->forCollection($collection)->create();

        $response = $this->get(route('collections.collection-images.edit', ['collection' => $collection, 'collection_image' => $collectionImage]));

        $response->assertRedirect(route('login'));
    }

    public function test_edit_page_passes_collection_and_image_from_controller(): void
    {
        $collection = Collection::factory()->create();
        $collectionImage = CollectionImage::factory()->forCollection($collection)->create();

        $response = $this->get(route('collections.collection-images.edit', ['collection' => $collection, 'collection_image' => $collectionImage]));

        $response->assertOk()
            ->assertViewIs('collection-images.edit')
            ->assertViewHas('collection', $collection)
            ->assertViewHas('collectionImage', $collectionImage);
    }

    public function test_edit_returns_404_for_image_belonging_to_different_collection(): void
    {
        $collection = Collection::factory()->create();
        $otherCollection = Collection::factory()->create();
        $collectionImage = CollectionImage::factory()->forCollection($otherCollection)->create();

        $response = $this->get(route('collections.collection-images.edit', ['collection' => $collection, 'collection_image' => $collectionImage]));

        $response->assertNotFound();
    }
}

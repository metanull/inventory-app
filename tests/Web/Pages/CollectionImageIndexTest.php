<?php

namespace Tests\Web\Pages;

use App\Models\Collection;
use App\Models\CollectionImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class CollectionImageIndexTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_index_renders_request_driven_page_without_livewire_markup(): void
    {
        $collection = Collection::factory()->create(['internal_name' => 'Test Collection']);
        CollectionImage::factory()->forCollection($collection)->create(['original_name' => 'artifact.jpg']);

        $response = $this->get(route('collections.collection-images.index', $collection));

        $response
            ->assertOk()
            ->assertViewIs('collection-images.index')
            ->assertSee('artifact.jpg');

        $this->assertStringNotContainsString('livewire:dynamic-component', $response->getContent());
        $this->assertStringNotContainsString('wire:click', $response->getContent());
    }

    public function test_index_scopes_images_to_parent_collection(): void
    {
        $collection = Collection::factory()->create(['internal_name' => 'My Collection']);
        $otherCollection = Collection::factory()->create(['internal_name' => 'Other Collection']);

        CollectionImage::factory()->forCollection($collection)->create(['original_name' => 'matching-image.jpg']);
        CollectionImage::factory()->forCollection($otherCollection)->create(['original_name' => 'other-image.jpg']);

        $response = $this->get(route('collections.collection-images.index', $collection));

        $response
            ->assertOk()
            ->assertSee('matching-image.jpg')
            ->assertDontSee('other-image.jpg');
    }

    public function test_index_returns_not_found_for_non_existent_collection(): void
    {
        $response = $this->get(route('collections.collection-images.index', ['collection' => 'non-existent-uuid']));

        $response->assertNotFound();
    }

    public function test_index_can_search_by_original_name(): void
    {
        $collection = Collection::factory()->create();

        CollectionImage::factory()->forCollection($collection)->create(['original_name' => 'artifact-photo.jpg']);
        CollectionImage::factory()->forCollection($collection)->create(['original_name' => 'other-photo.jpg']);

        $response = $this->get(route('collections.collection-images.index', ['collection' => $collection, 'q' => 'artifact-photo']));

        $response
            ->assertOk()
            ->assertSee('artifact-photo.jpg')
            ->assertDontSee('other-photo.jpg');
    }

    public function test_index_can_sort_by_display_order(): void
    {
        $collection = Collection::factory()->create();

        CollectionImage::factory()->forCollection($collection)->withOrder(2)->create(['original_name' => 'second.jpg']);
        CollectionImage::factory()->forCollection($collection)->withOrder(1)->create(['original_name' => 'first.jpg']);

        $response = $this->get(route('collections.collection-images.index', [
            'collection' => $collection,
            'sort' => 'display_order',
            'direction' => 'asc',
        ]));

        $response
            ->assertOk()
            ->assertSeeInOrder(['first.jpg', 'second.jpg']);
    }

    public function test_index_rejects_invalid_sort_field_gracefully(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->get(route('collections.collection-images.index', [
            'collection' => $collection,
            'sort' => 'invalid_field',
        ]));

        $response->assertOk();
    }

    public function test_index_requires_view_data_permission(): void
    {
        $collection = Collection::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('collections.collection-images.index', $collection));

        $response->assertForbidden();
    }

    public function test_index_passes_parent_collection_to_view(): void
    {
        $collection = Collection::factory()->create(['internal_name' => 'My Special Collection']);

        $response = $this->get(route('collections.collection-images.index', $collection));

        $response
            ->assertOk()
            ->assertSee('My Special Collection');

        $this->assertSame($collection->id, $response->viewData('collection')->id);
    }

    public function test_index_contains_upload_zone_component(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->get(route('collections.collection-images.index', $collection));

        $response
            ->assertOk()
            ->assertSee('imageUploadZone');
    }
}

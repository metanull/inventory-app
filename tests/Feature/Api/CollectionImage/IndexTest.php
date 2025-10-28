<?php

namespace Tests\Feature\Api\CollectionImage;

use App\Enums\Permission;
use App\Models\Collection;
use App\Models\CollectionImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class IndexTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith([Permission::VIEW_DATA->value]);
        $this->actingAs($this->user);
    }

    public function test_index_allows_authenticated_users(): void
    {
        $collection = Collection::factory()->create();
        $response = $this->getJson(route('collection.images.index', $collection->id));
        $response->assertOk();
    }

    public function test_index_returns_ok_when_no_data(): void
    {
        $collection = Collection::factory()->create();
        $response = $this->getJson(route('collection.images.index', $collection->id));
        $response->assertOk();
    }

    public function test_index_returns_an_empty_array_when_no_data(): void
    {
        $collection = Collection::factory()->create();
        $response = $this->getJson(route('collection.images.index', $collection->id));
        $response->assertJsonCount(0, 'data');
    }

    public function test_index_returns_the_expected_structure(): void
    {
        $collection = Collection::factory()->create();
        $collectionImage = CollectionImage::factory()->forCollection($collection)->create();
        $response = $this->getJson(route('collection.images.index', $collection->id));

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'collection_id',
                    'path',
                    'original_name',
                    'mime_type',
                    'size',
                    'alt_text',
                    'display_order',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    public function test_index_returns_collection_images_in_display_order(): void
    {
        $collection = Collection::factory()->create();
        $image3 = CollectionImage::factory()->forCollection($collection)->withOrder(3)->create();
        $image1 = CollectionImage::factory()->forCollection($collection)->withOrder(1)->create();
        $image2 = CollectionImage::factory()->forCollection($collection)->withOrder(2)->create();

        $response = $this->getJson(route('collection.images.index', $collection->id));

        $response->assertOk();
        $response->assertJsonCount(3, 'data');

        // Check that images are returned in display order
        $data = $response->json('data');
        $this->assertEquals($image1->id, $data[0]['id']);
        $this->assertEquals($image2->id, $data[1]['id']);
        $this->assertEquals($image3->id, $data[2]['id']);
    }

    public function test_index_only_returns_images_for_specified_collection(): void
    {
        $collection1 = Collection::factory()->create();
        $collection2 = Collection::factory()->create();

        $image1 = CollectionImage::factory()->forCollection($collection1)->create();
        $image2 = CollectionImage::factory()->forCollection($collection2)->create();

        $response = $this->getJson(route('collection.images.index', $collection1->id));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $this->assertEquals($image1->id, $response->json('data.0.id'));
    }

    public function test_index_returns_not_found_for_nonexistent_collection(): void
    {
        $response = $this->getJson(route('collection.images.index', 'nonexistent-uuid'));
        $response->assertNotFound();
    }

    public function test_index_returns_empty_for_collection_with_no_images(): void
    {
        $collection = Collection::factory()->create();
        $response = $this->getJson(route('collection.images.index', $collection->id));

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_index_includes_relationships_when_requested(): void
    {
        $collection = Collection::factory()->create();
        $collectionImage = CollectionImage::factory()->forCollection($collection)->create();

        $response = $this->getJson(route('collection.images.index', $collection->id).'?include=collection');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'collection_id',
                    'path',
                    'original_name',
                    'mime_type',
                    'size',
                    'alt_text',
                    'display_order',
                    'created_at',
                    'updated_at',
                    'collection' => [
                        'id',
                        'internal_name',
                        'type',
                    ],
                ],
            ],
        ]);
    }
}

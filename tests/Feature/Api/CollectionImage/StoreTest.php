<?php

namespace Tests\Feature\Api\CollectionImage;

use App\Enums\Permission;
use App\Models\Collection;
use App\Models\CollectionImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class StoreTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith(Permission::dataOperations());
        $this->actingAs($this->user);
    }

    public function test_store_creates_collection_image_successfully(): void
    {
        $collection = Collection::factory()->create();
        $data = [
            'path' => 'https://example.com/test-image.jpg',
            'original_name' => 'test-image.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 150000,
            'alt_text' => 'Test image description',
            'display_order' => 1,
        ];

        $response = $this->postJson(route('collection.images.store', $collection->id), $data);

        $response->assertCreated();
        $this->assertDatabaseHas('collection_images', [
            'collection_id' => $collection->id,
            'path' => $data['path'],
            'original_name' => $data['original_name'],
            'mime_type' => $data['mime_type'],
            'size' => $data['size'],
            'alt_text' => $data['alt_text'],
            'display_order' => $data['display_order'],
        ]);
    }

    public function test_store_returns_correct_structure(): void
    {
        $collection = Collection::factory()->create();
        $data = CollectionImage::factory()->make()->toArray();
        unset($data['collection_id']); // Remove collection_id as it's provided in the route

        $response = $this->postJson(route('collection.images.store', $collection->id), $data);

        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => [
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
        ]);
    }

    public function test_store_auto_assigns_display_order_when_not_provided(): void
    {
        $collection = Collection::factory()->create();

        // Create existing images
        CollectionImage::factory()->forCollection($collection)->withOrder(1)->create();
        CollectionImage::factory()->forCollection($collection)->withOrder(2)->create();

        $data = [
            'path' => 'https://example.com/test-image.jpg',
            'original_name' => 'test-image.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 150000,
            'alt_text' => 'Test image description',
        ];

        $response = $this->postJson(route('collection.images.store', $collection->id), $data);

        $response->assertCreated();
        $response->assertJsonPath('data.display_order', 3);
    }

    public function test_store_validates_required_fields(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->postJson(route('collection.images.store', $collection->id), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['path', 'original_name', 'mime_type', 'size']);
    }

    public function test_store_validates_path_length(): void
    {
        $collection = Collection::factory()->create();
        $data = CollectionImage::factory()->make()->toArray();
        $data['path'] = str_repeat('a', 501); // Max is 500

        $response = $this->postJson(route('collection.images.store', $collection->id), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['path']);
    }

    public function test_store_validates_mime_type(): void
    {
        $collection = Collection::factory()->create();
        $data = CollectionImage::factory()->make()->toArray();
        $data['mime_type'] = 'invalid-mime-type';

        $response = $this->postJson(route('collection.images.store', $collection->id), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['mime_type']);
    }

    public function test_store_validates_size_is_positive_integer(): void
    {
        $collection = Collection::factory()->create();
        $data = CollectionImage::factory()->make()->toArray();
        $data['size'] = -100;

        $response = $this->postJson(route('collection.images.store', $collection->id), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['size']);
    }

    public function test_store_validates_display_order_is_positive_integer(): void
    {
        $collection = Collection::factory()->create();
        $data = CollectionImage::factory()->make()->toArray();
        $data['display_order'] = 0;

        $response = $this->postJson(route('collection.images.store', $collection->id), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['display_order']);
    }

    public function test_store_allows_null_alt_text(): void
    {
        $collection = Collection::factory()->create();
        $data = CollectionImage::factory()->make(['alt_text' => null])->toArray();

        $response = $this->postJson(route('collection.images.store', $collection->id), $data);

        $response->assertCreated();
        $response->assertJsonPath('data.alt_text', null);
    }

    public function test_store_validates_alt_text_max_length(): void
    {
        $collection = Collection::factory()->create();
        $data = CollectionImage::factory()->make()->toArray();
        $data['alt_text'] = str_repeat('a', 501); // Assuming max length is 500

        $response = $this->postJson(route('collection.images.store', $collection->id), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['alt_text']);
    }

    public function test_store_returns_not_found_for_nonexistent_collection(): void
    {
        $data = CollectionImage::factory()->make()->toArray();

        $response = $this->postJson(route('collection.images.store', 'nonexistent-uuid'), $data);

        $response->assertNotFound();
    }

    public function test_store_creates_multiple_images_for_same_collection(): void
    {
        $collection = Collection::factory()->create();

        for ($i = 1; $i <= 3; $i++) {
            $data = [
                'path' => "https://example.com/test-image-{$i}.jpg",
                'original_name' => "test-image-{$i}.jpg",
                'mime_type' => 'image/jpeg',
                'size' => 150000 + $i,
                'alt_text' => "Test image {$i} description",
                'display_order' => $i,
            ];

            $response = $this->postJson(route('collection.images.store', $collection->id), $data);
            $response->assertCreated();
        }

        $this->assertEquals(3, $collection->collectionImages()->count());
    }
}

<?php

namespace Tests\Feature\Api\CollectionImage;

use App\Enums\Permission;
use App\Models\Collection;
use App\Models\CollectionImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class UpdateTest extends TestCase
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

    public function test_update_modifies_collection_image_successfully(): void
    {
        $collectionImage = CollectionImage::factory()->create([
            'alt_text' => 'Original alt text',
            'display_order' => 1,
        ]);

        $data = [
            'alt_text' => 'Updated alt text',
            'display_order' => 2,
        ];

        $response = $this->patchJson(route('collection-image.update', $collectionImage->id), $data);

        $response->assertOk();
        $this->assertDatabaseHas('collection_images', [
            'id' => $collectionImage->id,
            'alt_text' => 'Updated alt text',
            'display_order' => 2,
        ]);
    }

    public function test_update_returns_correct_structure(): void
    {
        $collectionImage = CollectionImage::factory()->create();
        $data = ['alt_text' => 'Updated alt text'];

        $response = $this->patchJson(route('collection-image.update', $collectionImage->id), $data);

        $response->assertOk();
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

    public function test_update_allows_partial_updates(): void
    {
        $collectionImage = CollectionImage::factory()->create([
            'alt_text' => 'Original alt text',
            'display_order' => 1,
        ]);

        $response = $this->patchJson(route('collection-image.update', $collectionImage->id), [
            'alt_text' => 'Only alt text updated',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.alt_text', 'Only alt text updated');
        $response->assertJsonPath('data.display_order', 1); // Should remain unchanged
    }

    public function test_update_validates_display_order_is_positive(): void
    {
        $collectionImage = CollectionImage::factory()->create();

        $response = $this->patchJson(route('collection-image.update', $collectionImage->id), [
            'display_order' => 0,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['display_order']);
    }

    public function test_update_validates_alt_text_max_length(): void
    {
        $collectionImage = CollectionImage::factory()->create();

        $response = $this->patchJson(route('collection-image.update', $collectionImage->id), [
            'alt_text' => str_repeat('a', 501), // Assuming max length is 500
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['alt_text']);
    }

    public function test_update_allows_null_alt_text(): void
    {
        $collectionImage = CollectionImage::factory()->create(['alt_text' => 'Some text']);

        $response = $this->patchJson(route('collection-image.update', $collectionImage->id), [
            'alt_text' => null,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.alt_text', null);
    }

    public function test_update_prevents_changing_immutable_fields(): void
    {
        $collectionImage = CollectionImage::factory()->create();
        $originalPath = $collectionImage->path;
        $originalCollectionId = $collectionImage->collection_id;

        $response = $this->patchJson(route('collection-image.update', $collectionImage->id), [
            'path' => 'https://example.com/new-path.jpg',
            'collection_id' => Collection::factory()->create()->id,
            'original_name' => 'new-name.jpg',
            'mime_type' => 'image/png',
            'size' => 999999,
        ]);

        // Should succeed but ignore immutable fields
        $response->assertOk();

        $collectionImage->refresh();
        $this->assertEquals($originalPath, $collectionImage->path);
        $this->assertEquals($originalCollectionId, $collectionImage->collection_id);
    }

    public function test_update_returns_not_found_for_nonexistent_collection_image(): void
    {
        $response = $this->patchJson(route('collection-image.update', 'nonexistent-uuid'), [
            'alt_text' => 'Updated text',
        ]);

        $response->assertNotFound();
    }

    public function test_update_handles_display_order_conflicts(): void
    {
        $collection = Collection::factory()->create();
        $image1 = CollectionImage::factory()->forCollection($collection)->withOrder(1)->create();
        $image2 = CollectionImage::factory()->forCollection($collection)->withOrder(2)->create();
        $image3 = CollectionImage::factory()->forCollection($collection)->withOrder(3)->create();

        // Update image3 to have the same order as image1
        $response = $this->patchJson(route('collection-image.update', $image3->id), [
            'display_order' => 1,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.display_order', 1);

        $this->assertDatabaseHas('collection_images', [
            'id' => $image3->id,
            'display_order' => 1,
        ]);
    }

    public function test_update_with_include_parameter(): void
    {
        $collectionImage = CollectionImage::factory()->create();

        $response = $this->patchJson(
            route('collection-image.update', $collectionImage->id).'?include=collection',
            ['alt_text' => 'Updated with collection']
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'collection_id',
                'alt_text',
                'collection' => [
                    'id',
                    'internal_name',
                    'type',
                ],
            ],
        ]);
    }

    public function test_update_preserves_unchanged_fields(): void
    {
        $collectionImage = CollectionImage::factory()->create([
            'alt_text' => 'Original alt text',
            'display_order' => 5,
        ]);

        $originalAltText = $collectionImage->alt_text;
        $originalDisplayOrder = $collectionImage->display_order;

        // Update only alt_text
        $response = $this->patchJson(route('collection-image.update', $collectionImage->id), [
            'alt_text' => 'New alt text',
        ]);

        $response->assertOk();

        $collectionImage->refresh();

        // Verify the update occurred
        $this->assertNotEquals($originalAltText, $collectionImage->alt_text);
        $this->assertEquals('New alt text', $collectionImage->alt_text);
        $this->assertEquals($originalDisplayOrder, $collectionImage->display_order); // Unchanged

        // Verify database state
        $this->assertDatabaseHas('collection_images', [
            'id' => $collectionImage->id,
            'alt_text' => 'New alt text',
            'display_order' => 5,
        ]);
    }
}

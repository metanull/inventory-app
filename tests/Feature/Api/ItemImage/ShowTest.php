<?php

namespace Tests\Feature\Api\ItemImage;

use App\Models\ItemImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class ShowTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createVisitorUser();
        $this->actingAs($this->user);
    }

    public function test_show_allows_authenticated_users(): void
    {
        $itemImage = ItemImage::factory()->create();
        $response = $this->getJson(route('item-image.show', $itemImage->id));
        $response->assertOk();
    }

    public function test_show_returns_the_expected_structure(): void
    {
        $itemImage = ItemImage::factory()->create();
        $response = $this->getJson(route('item-image.show', $itemImage->id));

        $response->assertJsonStructure([
            'data' => [
                'id',
                'item_id',
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

    public function test_show_returns_correct_item_image_data(): void
    {
        $itemImage = ItemImage::factory()->create([
            'alt_text' => 'Test Alt Text',
            'display_order' => 3,
        ]);

        $response = $this->getJson(route('item-image.show', $itemImage->id));

        $response->assertOk();
        $response->assertJsonPath('data.id', $itemImage->id);
        $response->assertJsonPath('data.item_id', $itemImage->item_id);
        $response->assertJsonPath('data.alt_text', 'Test Alt Text');
        $response->assertJsonPath('data.display_order', 3);
    }

    public function test_show_returns_not_found_for_nonexistent_item_image(): void
    {
        $response = $this->getJson(route('item-image.show', 'nonexistent-uuid'));
        $response->assertNotFound();
    }

    public function test_show_includes_relationships_when_requested(): void
    {
        $itemImage = ItemImage::factory()->create();

        $response = $this->getJson(route('item-image.show', $itemImage->id).'?include=item');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'item_id',
                'path',
                'original_name',
                'mime_type',
                'size',
                'alt_text',
                'display_order',
                'created_at',
                'updated_at',
                'item' => [
                    'id',
                    'internal_name',
                    'type',
                ],
            ],
        ]);
    }

    public function test_show_returns_consistent_data_structure(): void
    {
        $itemImages = ItemImage::factory()->count(3)->create();

        foreach ($itemImages as $itemImage) {
            $response = $this->getJson(route('item-image.show', $itemImage->id));

            $response->assertOk();

            // Verify all required fields are present
            $data = $response->json('data');
            $this->assertArrayHasKey('id', $data);
            $this->assertArrayHasKey('item_id', $data);
            $this->assertArrayHasKey('path', $data);
            $this->assertArrayHasKey('original_name', $data);
            $this->assertArrayHasKey('mime_type', $data);
            $this->assertArrayHasKey('size', $data);
            $this->assertArrayHasKey('display_order', $data);
            $this->assertArrayHasKey('created_at', $data);
            $this->assertArrayHasKey('updated_at', $data);

            // alt_text can be null, so just check it exists as a key
            $this->assertArrayHasKey('alt_text', $data);
        }
    }
}

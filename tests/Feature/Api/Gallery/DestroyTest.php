<?php

namespace Tests\Feature\Api\Gallery;

use App\Models\Context;
use App\Models\Detail;
use App\Models\Gallery;
use App\Models\GalleryTranslation;
use App\Models\Item;
use App\Models\Language;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Gallery Destroy Test
 *
 * Tests the gallery deletion API endpoint.
 * Verifies proper deletion functionality and cascade behavior.
 */
class DestroyTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /**
     * Test that authenticated users can delete galleries.
     */
    public function test_authenticated_user_can_delete_gallery(): void
    {
        $gallery = Gallery::factory()->create();

        $response = $this->deleteJson(route('gallery.destroy', $gallery));

        $response->assertNoContent();
        $this->assertDatabaseMissing('galleries', [
            'id' => $gallery->id,
        ]);
    }

    /**
     * Test gallery deletion removes gallery from database.
     */
    public function test_gallery_deletion_removes_gallery_from_database(): void
    {
        $gallery = Gallery::factory()->create();
        $galleryId = $gallery->id;

        $this->assertDatabaseHas('galleries', [
            'id' => $galleryId,
        ]);

        $response = $this->deleteJson(route('gallery.destroy', $gallery));

        $response->assertNoContent();
        $this->assertDatabaseMissing('galleries', [
            'id' => $galleryId,
        ]);
    }

    /**
     * Test gallery deletion cascades to translations.
     */
    public function test_gallery_deletion_cascades_to_translations(): void
    {
        $language = Language::factory()->create();
        $context = Context::factory()->create();
        $gallery = Gallery::factory()->create();

        $translation = GalleryTranslation::factory()
            ->forGallery($gallery)
            ->forLanguage($language)
            ->forContext($context)
            ->create();

        $this->assertDatabaseHas('gallery_translations', [
            'id' => $translation->id,
            'gallery_id' => $gallery->id,
        ]);

        $response = $this->deleteJson(route('gallery.destroy', $gallery));

        $response->assertNoContent();
        $this->assertDatabaseMissing('galleries', [
            'id' => $gallery->id,
        ]);
        $this->assertDatabaseMissing('gallery_translations', [
            'id' => $translation->id,
            'gallery_id' => $gallery->id,
        ]);
    }

    /**
     * Test gallery deletion removes partner relationships.
     */
    public function test_gallery_deletion_removes_partner_relationships(): void
    {
        $gallery = Gallery::factory()->create();
        $partner = Partner::factory()->create();

        $gallery->partners()->attach($partner->id, [
            'level' => 'partner',
            'backward_compatibility' => null,
        ]);

        $this->assertDatabaseHas('gallery_partner', [
            'gallery_id' => $gallery->id,
            'partner_id' => $partner->id,
        ]);

        $response = $this->deleteJson(route('gallery.destroy', $gallery));

        $response->assertNoContent();
        $this->assertDatabaseMissing('gallery_partner', [
            'gallery_id' => $gallery->id,
            'partner_id' => $partner->id,
        ]);

        // Partner should still exist
        $this->assertDatabaseHas('partners', [
            'id' => $partner->id,
        ]);
    }

    /**
     * Test gallery deletion removes item and detail relationships.
     */
    public function test_gallery_deletion_removes_item_and_detail_relationships(): void
    {
        $gallery = Gallery::factory()->create();
        $item = Item::factory()->create();
        $detail = Detail::factory()->create();

        $gallery->items()->attach($item->id, [
            'order' => 1,
            'backward_compatibility' => null,
        ]);

        $gallery->details()->attach($detail->id, [
            'order' => 2,
            'backward_compatibility' => null,
        ]);

        $this->assertDatabaseHas('galleryables', [
            'gallery_id' => $gallery->id,
            'galleryable_id' => $item->id,
            'galleryable_type' => Item::class,
        ]);

        $this->assertDatabaseHas('galleryables', [
            'gallery_id' => $gallery->id,
            'galleryable_id' => $detail->id,
            'galleryable_type' => Detail::class,
        ]);

        $response = $this->deleteJson(route('gallery.destroy', $gallery));

        $response->assertNoContent();
        $this->assertDatabaseMissing('galleryables', [
            'gallery_id' => $gallery->id,
        ]);

        // Items and details should still exist
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
        ]);
        $this->assertDatabaseHas('details', [
            'id' => $detail->id,
        ]);
    }

    /**
     * Test gallery deletion returns 404 for non-existent gallery.
     */
    public function test_gallery_deletion_returns_404_for_non_existent_gallery(): void
    {
        $response = $this->deleteJson(route('gallery.destroy', ['gallery' => 'non-existent-id']));

        $response->assertNotFound();
    }

    /**
     * Test gallery deletion is idempotent.
     */
    public function test_gallery_deletion_is_idempotent(): void
    {
        $gallery = Gallery::factory()->create();
        $galleryId = $gallery->id;

        // First deletion
        $response1 = $this->deleteJson(route('gallery.destroy', $gallery));
        $response1->assertNoContent();
        $this->assertDatabaseMissing('galleries', [
            'id' => $galleryId,
        ]);

        // Second deletion attempt
        $response2 = $this->deleteJson(route('gallery.destroy', ['gallery' => $galleryId]));
        $response2->assertNotFound();
    }

    /**
     * Test multiple gallery deletions.
     */
    public function test_multiple_gallery_deletions(): void
    {
        $galleries = Gallery::factory()->count(3)->create();

        foreach ($galleries as $gallery) {
            $response = $this->deleteJson(route('gallery.destroy', $gallery));
            $response->assertNoContent();
            $this->assertDatabaseMissing('galleries', [
                'id' => $gallery->id,
            ]);
        }

        $this->assertEquals(0, Gallery::count());
    }

    /**
     * Test gallery deletion with complex relationships.
     */
    public function test_gallery_deletion_with_complex_relationships(): void
    {
        $language = Language::factory()->create();
        $context = Context::factory()->create();
        $gallery = Gallery::factory()->create();
        $partner = Partner::factory()->create();
        $item = Item::factory()->create();
        $detail = Detail::factory()->create();

        // Create translation
        $translation = GalleryTranslation::factory()
            ->forGallery($gallery)
            ->forLanguage($language)
            ->forContext($context)
            ->create();

        // Attach partner
        $gallery->partners()->attach($partner->id, [
            'level' => 'partner',
            'backward_compatibility' => null,
        ]);

        // Attach items and details
        $gallery->items()->attach($item->id, [
            'order' => 1,
            'backward_compatibility' => null,
        ]);
        $gallery->details()->attach($detail->id, [
            'order' => 2,
            'backward_compatibility' => null,
        ]);

        $response = $this->deleteJson(route('gallery.destroy', $gallery));

        $response->assertNoContent();

        // Gallery should be deleted
        $this->assertDatabaseMissing('galleries', [
            'id' => $gallery->id,
        ]);

        // All relationships should be removed
        $this->assertDatabaseMissing('gallery_translations', [
            'gallery_id' => $gallery->id,
        ]);
        $this->assertDatabaseMissing('gallery_partner', [
            'gallery_id' => $gallery->id,
        ]);
        $this->assertDatabaseMissing('galleryables', [
            'gallery_id' => $gallery->id,
        ]);

        // Related models should still exist
        $this->assertDatabaseHas('languages', ['id' => $language->id]);
        $this->assertDatabaseHas('contexts', ['id' => $context->id]);
        $this->assertDatabaseHas('partners', ['id' => $partner->id]);
        $this->assertDatabaseHas('items', ['id' => $item->id]);
        $this->assertDatabaseHas('details', ['id' => $detail->id]);
    }
}

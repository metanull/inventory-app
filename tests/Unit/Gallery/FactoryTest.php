<?php

namespace Tests\Unit\Gallery;

use App\Models\Context;
use App\Models\Detail;
use App\Models\Gallery;
use App\Models\GalleryTranslation;
use App\Models\Item;
use App\Models\Language;
use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Gallery Factory Unit Test
 *
 * Tests the Gallery factory to ensure it generates valid data
 * that complies with model constraints and database schema.
 */
class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test that the gallery factory creates a valid gallery.
     */
    public function test_gallery_factory_creates_valid_gallery(): void
    {
        $gallery = Gallery::factory()->create();

        $this->assertDatabaseHas('galleries', [
            'id' => $gallery->id,
            'internal_name' => $gallery->internal_name,
        ]);

        // Test data constraints
        $this->assertIsString($gallery->id);
        $this->assertIsString($gallery->internal_name);
        $this->assertNotEmpty($gallery->internal_name);
        $this->assertNull($gallery->backward_compatibility);
        $this->assertInstanceOf(\Carbon\Carbon::class, $gallery->created_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $gallery->updated_at);
    }

    /**
     * Test that the gallery factory can create a gallery with backward compatibility.
     */
    public function test_gallery_factory_with_backward_compatibility(): void
    {
        $gallery = Gallery::factory()->withBackwardCompatibility()->create();

        $this->assertDatabaseHas('galleries', [
            'id' => $gallery->id,
            'backward_compatibility' => $gallery->backward_compatibility,
        ]);

        $this->assertNotNull($gallery->backward_compatibility);
        $this->assertIsString($gallery->backward_compatibility);
    }

    /**
     * Test that the gallery factory generates unique internal names.
     */
    public function test_gallery_factory_generates_unique_internal_names(): void
    {
        $gallery1 = Gallery::factory()->create();
        $gallery2 = Gallery::factory()->create();

        $this->assertNotEquals($gallery1->internal_name, $gallery2->internal_name);
    }

    /**
     * Test that the gallery factory can be used to create multiple galleries.
     */
    public function test_gallery_factory_creates_multiple_galleries(): void
    {
        $galleries = Gallery::factory()->count(5)->create();

        $this->assertCount(5, $galleries);
        $this->assertEquals(5, Gallery::count());

        foreach ($galleries as $gallery) {
            $this->assertDatabaseHas('galleries', [
                'id' => $gallery->id,
            ]);
        }
    }

    /**
     * Test that galleries can have translations.
     */
    public function test_gallery_can_have_translations(): void
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
            'language_id' => $language->id,
            'context_id' => $context->id,
        ]);

        $gallery->refresh();
        $this->assertCount(1, $gallery->translations);
        $this->assertEquals($translation->id, $gallery->translations->first()->id);
    }

    /**
     * Test that galleries can have partners with different levels.
     */
    public function test_gallery_can_have_partners(): void
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
            'level' => 'partner',
        ]);

        $gallery->refresh();
        $this->assertCount(1, $gallery->partners);
        $this->assertEquals($partner->id, $gallery->partners->first()->id);
    }

    /**
     * Test that galleries can have items and details.
     */
    public function test_gallery_can_have_items_and_details(): void
    {
        $gallery = Gallery::factory()->create();
        $item = Item::factory()->create();
        $detail = Detail::factory()->create();

        // Attach item
        $gallery->items()->attach($item->id, [
            'order' => 1,
            'backward_compatibility' => null,
        ]);

        // Attach detail
        $gallery->details()->attach($detail->id, [
            'order' => 2,
            'backward_compatibility' => null,
        ]);

        $this->assertDatabaseHas('galleryables', [
            'gallery_id' => $gallery->id,
            'galleryable_id' => $item->id,
            'galleryable_type' => Item::class,
            'order' => 1,
        ]);

        $this->assertDatabaseHas('galleryables', [
            'gallery_id' => $gallery->id,
            'galleryable_id' => $detail->id,
            'galleryable_type' => Detail::class,
            'order' => 2,
        ]);

        $gallery->refresh();
        $this->assertCount(1, $gallery->items);
        $this->assertCount(1, $gallery->details);
        $this->assertEquals($item->id, $gallery->items->first()->id);
        $this->assertEquals($detail->id, $gallery->details->first()->id);
    }

    /**
     * Test that the factory respects the model's fillable attributes.
     */
    public function test_gallery_factory_respects_fillable_attributes(): void
    {
        $attributes = Gallery::factory()->make()->toArray();
        $fillable = (new Gallery)->getFillable();

        foreach (array_keys($attributes) as $attribute) {
            if ($attribute !== 'id') { // ID is auto-generated
                $this->assertContains($attribute, $fillable, "Attribute '$attribute' should be fillable");
            }
        }
    }
}

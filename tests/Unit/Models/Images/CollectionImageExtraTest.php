<?php

namespace Tests\Unit\Models\Images;

use App\Models\CollectionImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The sponsor-logo metadata carried on exhibition images.
 *
 * Legacy sponsor logos hold a caption, a hyperlink and a banner category that
 * had nowhere to live before `collection_images.extra` existed, so the
 * importer silently dropped them. See story #1592.
 */
class CollectionImageExtraTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_images_table_has_an_extra_column(): void
    {
        $this->assertTrue(
            Schema::hasColumn('collection_images', 'extra'),
            'collection_images must carry an `extra` JSON column'
        );
    }

    public function test_extra_is_fillable(): void
    {
        $this->assertContains('extra', (new CollectionImage)->getFillable());
    }

    public function test_extra_defaults_to_null(): void
    {
        $image = CollectionImage::factory()->create();

        $this->assertNull($image->extra);
        $this->assertNull($image->fresh()?->extra);
    }

    public function test_an_array_round_trips_through_the_array_cast(): void
    {
        $payload = [
            'link' => 'https://www.unaoc.org/',
            'category_id' => 2,
            'category_name' => 'Footer 2',
            'visible' => true,
        ];

        $image = CollectionImage::factory()->create(['extra' => $payload]);

        $fresh = $image->fresh();

        $this->assertNotNull($fresh);
        $this->assertIsArray($fresh->extra);
        $this->assertSame($payload, $fresh->extra);
    }

    public function test_nested_language_keyed_payloads_survive_a_round_trip(): void
    {
        $payload = [
            'link' => 'https://www.unaoc.org/',
            'category_id' => 2,
            'category_name' => 'Footer 2',
            'visible' => true,
            'labels' => [
                'en' => 'United Nations Alliance of Civilizations',
                'fr' => 'Alliance des civilisations des Nations unies',
            ],
            'alt_texts' => [
                'en' => 'UNAOC logo',
            ],
        ];

        $image = CollectionImage::factory()->create(['extra' => $payload]);

        $this->assertSame($payload, $image->fresh()?->extra);
    }

    public function test_unicode_payloads_survive_a_round_trip(): void
    {
        $payload = [
            'labels' => [
                'ar' => 'تحالف الحضارات للأمم المتحدة',
                'el' => 'Συμμαχία Πολιτισμών',
                'de' => 'Bündnis der Zivilisationen',
            ],
        ];

        $image = CollectionImage::factory()->create(['extra' => $payload]);

        $fresh = $image->fresh();

        $this->assertNotNull($fresh);
        $this->assertSame($payload, $fresh->extra);
        $this->assertSame('تحالف الحضارات للأمم المتحدة', $fresh->extra['labels']['ar']);
    }

    public function test_extra_can_be_updated_and_cleared(): void
    {
        $image = CollectionImage::factory()->create(['extra' => ['category_id' => 2]]);

        $image->update(['extra' => ['category_id' => 3]]);
        $this->assertSame(['category_id' => 3], $image->fresh()?->extra);

        $image->update(['extra' => null]);
        $this->assertNull($image->fresh()?->extra);
    }
}

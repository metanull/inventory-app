<?php

namespace Tests\Unit\Models;

use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_theme_type(): void
    {
        $collection = Collection::factory()->theme()->create();

        $this->assertEquals('theme', $collection->type);
    }

    public function test_factory_creates_exhibition_trail_type(): void
    {
        $collection = Collection::factory()->exhibitionTrail()->create();

        $this->assertEquals('exhibition trail', $collection->type);
    }

    public function test_factory_creates_itinerary_type(): void
    {
        $collection = Collection::factory()->itinerary()->create();

        $this->assertEquals('itinerary', $collection->type);
    }

    public function test_factory_creates_location_type(): void
    {
        $collection = Collection::factory()->location()->create();

        $this->assertEquals('location', $collection->type);
    }

    public function test_scope_themes_returns_only_theme_type(): void
    {
        Collection::factory()->theme()->create();
        Collection::factory()->collection()->create();
        Collection::factory()->exhibition()->create();

        $themes = Collection::themes()->get();

        $this->assertCount(1, $themes);
        $this->assertEquals('theme', $themes->first()->type);
    }

    public function test_scope_exhibition_trails_returns_only_exhibition_trail_type(): void
    {
        Collection::factory()->exhibitionTrail()->create();
        Collection::factory()->collection()->create();

        $trails = Collection::exhibitionTrails()->get();

        $this->assertCount(1, $trails);
        $this->assertEquals('exhibition trail', $trails->first()->type);
    }

    public function test_scope_itineraries_returns_only_itinerary_type(): void
    {
        Collection::factory()->itinerary()->create();
        Collection::factory()->collection()->create();

        $itineraries = Collection::itineraries()->get();

        $this->assertCount(1, $itineraries);
        $this->assertEquals('itinerary', $itineraries->first()->type);
    }

    public function test_scope_locations_returns_only_location_type(): void
    {
        Collection::factory()->location()->create();
        Collection::factory()->collection()->create();

        $locations = Collection::locations()->get();

        $this->assertCount(1, $locations);
        $this->assertEquals('location', $locations->first()->type);
    }

    public function test_all_type_constants_are_valid(): void
    {
        $types = [
            Collection::TYPE_COLLECTION,
            Collection::TYPE_EXHIBITION,
            Collection::TYPE_GALLERY,
            Collection::TYPE_THEME,
            Collection::TYPE_EXHIBITION_TRAIL,
            Collection::TYPE_ITINERARY,
            Collection::TYPE_LOCATION,
        ];

        $expected = ['collection', 'exhibition', 'gallery', 'theme', 'exhibition trail', 'itinerary', 'location'];

        $this->assertEquals($expected, $types);
    }
}

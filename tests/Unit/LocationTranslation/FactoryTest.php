<?php

namespace Tests\Unit\LocationTranslation;

use App\Models\LocationTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_valid_translation()
    {
        $translation = LocationTranslation::factory()->withoutLocationTranslations()->create();

        $this->assertDatabaseHas('location_translations', [
            'id' => $translation->id,
            'location_id' => $translation->location_id,
            'language_id' => $translation->language_id,
            'name' => $translation->name,
        ]);

        $this->assertNotNull($translation->location);
        $this->assertNotNull($translation->language);
    }
}

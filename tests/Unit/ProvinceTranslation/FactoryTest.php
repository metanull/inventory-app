<?php

namespace Tests\Unit\ProvinceTranslation;

use App\Models\ProvinceTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_valid_translation()
    {
        $translation = ProvinceTranslation::factory()->withoutProvinceTranslations()->create();

        $this->assertDatabaseHas('province_translations', [
            'id' => $translation->id,
            'province_id' => $translation->province_id,
            'language_id' => $translation->language_id,
            'name' => $translation->name,
        ]);

        $this->assertNotNull($translation->province);
        $this->assertNotNull($translation->language);
    }
}

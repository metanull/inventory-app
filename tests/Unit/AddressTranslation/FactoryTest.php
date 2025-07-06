<?php

namespace Tests\Unit\AddressTranslation;

use App\Models\AddressTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_valid_translation()
    {
        $translation = AddressTranslation::factory()->withoutAddressTranslations()->create();

        $this->assertDatabaseHas('address_translations', [
            'id' => $translation->id,
            'address_id' => $translation->address_id,
            'language_id' => $translation->language_id,
            'address' => $translation->address,
        ]);

        $this->assertNotNull($translation->address);
        $this->assertNotNull($translation->language);
    }
}

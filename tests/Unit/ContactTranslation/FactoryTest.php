<?php

namespace Tests\Unit\ContactTranslation;

use App\Models\ContactTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_valid_translation()
    {
        $translation = ContactTranslation::factory()->withoutContactTranslations()->create();

        $this->assertDatabaseHas('contact_translations', [
            'id' => $translation->id,
            'contact_id' => $translation->contact_id,
            'language_id' => $translation->language_id,
            'label' => $translation->label,
        ]);

        $this->assertNotNull($translation->contact);
        $this->assertNotNull($translation->language);
    }
}

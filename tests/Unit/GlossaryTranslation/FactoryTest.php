<?php

namespace Tests\Unit\GlossaryTranslation;

use App\Models\Glossary;
use App\Models\GlossaryTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_valid_glossary_translation(): void
    {
        $translation = GlossaryTranslation::factory()->create();

        $this->assertInstanceOf(GlossaryTranslation::class, $translation);
        $this->assertNotNull($translation->id);
        $this->assertNotNull($translation->glossary_id);
        $this->assertNotNull($translation->language_id);
        $this->assertNotNull($translation->definition);
        $this->assertIsString($translation->definition);
    }

    public function test_factory_creates_translation_in_database(): void
    {
        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();

        $translation = GlossaryTranslation::factory()->create([
            'glossary_id' => $glossary->id,
            'language_id' => $language->id,
            'definition' => 'Test definition for this term',
        ]);

        $this->assertDatabaseHas('glossary_translations', [
            'id' => $translation->id,
            'glossary_id' => $glossary->id,
            'language_id' => $language->id,
            'definition' => 'Test definition for this term',
        ]);
    }

    public function test_translation_belongs_to_glossary(): void
    {
        $glossary = Glossary::factory()->create();
        $translation = GlossaryTranslation::factory()->for($glossary)->create();

        $this->assertInstanceOf(Glossary::class, $translation->glossary);
        $this->assertEquals($glossary->id, $translation->glossary->id);
    }

    public function test_translation_belongs_to_language(): void
    {
        $language = Language::factory()->create();
        $translation = GlossaryTranslation::factory()->for($language)->create();

        $this->assertInstanceOf(Language::class, $translation->language);
        $this->assertEquals($language->id, $translation->language->id);
    }

    public function test_translation_uses_uuid_for_primary_key(): void
    {
        $translation = GlossaryTranslation::factory()->create();

        $this->assertIsString($translation->id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $translation->id
        );
    }

    public function test_scope_for_language_filters_translations(): void
    {
        $language1 = Language::factory()->create();
        $language2 = Language::factory()->create();
        $glossary = Glossary::factory()->create();

        $translation1 = GlossaryTranslation::factory()->for($glossary)->for($language1)->create();
        GlossaryTranslation::factory()->for($glossary)->for($language2)->create();

        $filtered = GlossaryTranslation::forLanguage($language1->id)->get();

        $this->assertEquals(1, $filtered->count());
        $this->assertEquals($translation1->id, $filtered->first()->id);
    }
}

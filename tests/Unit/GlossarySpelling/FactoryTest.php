<?php

namespace Tests\Unit\GlossarySpelling;

use App\Models\Glossary;
use App\Models\GlossarySpelling;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_valid_glossary_spelling(): void
    {
        $spelling = GlossarySpelling::factory()->create();

        $this->assertInstanceOf(GlossarySpelling::class, $spelling);
        $this->assertNotNull($spelling->id);
        $this->assertNotNull($spelling->glossary_id);
        $this->assertNotNull($spelling->language_id);
        $this->assertNotNull($spelling->spelling);
        $this->assertIsString($spelling->spelling);
    }

    public function test_factory_creates_spelling_in_database(): void
    {
        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();

        $spelling = GlossarySpelling::factory()->create([
            'glossary_id' => $glossary->id,
            'language_id' => $language->id,
            'spelling' => 'test-spelling-variant',
        ]);

        $this->assertDatabaseHas('glossary_spellings', [
            'id' => $spelling->id,
            'glossary_id' => $glossary->id,
            'language_id' => $language->id,
            'spelling' => 'test-spelling-variant',
        ]);
    }

    public function test_spelling_belongs_to_glossary(): void
    {
        $glossary = Glossary::factory()->create();
        $spelling = GlossarySpelling::factory()->for($glossary)->create();

        $this->assertInstanceOf(Glossary::class, $spelling->glossary);
        $this->assertEquals($glossary->id, $spelling->glossary->id);
    }

    public function test_spelling_belongs_to_language(): void
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->for($language)->create();

        $this->assertInstanceOf(Language::class, $spelling->language);
        $this->assertEquals($language->id, $spelling->language->id);
    }

    public function test_spelling_uses_uuid_for_primary_key(): void
    {
        $spelling = GlossarySpelling::factory()->create();

        $this->assertIsString($spelling->id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $spelling->id
        );
    }

    public function test_scope_for_language_filters_spellings(): void
    {
        $language1 = Language::factory()->create();
        $language2 = Language::factory()->create();
        $glossary = Glossary::factory()->create();

        $spelling1 = GlossarySpelling::factory()->for($glossary)->for($language1)->create();
        GlossarySpelling::factory()->for($glossary)->for($language2)->create();

        $filtered = GlossarySpelling::forLanguage($language1->id)->get();

        $this->assertEquals(1, $filtered->count());
        $this->assertEquals($spelling1->id, $filtered->first()->id);
    }

    public function test_scope_for_spelling_filters_by_spelling_text(): void
    {
        $glossary = Glossary::factory()->create();
        $spelling1 = GlossarySpelling::factory()->for($glossary)->create(['spelling' => 'variant-a']);
        GlossarySpelling::factory()->for($glossary)->create(['spelling' => 'variant-b']);

        $filtered = GlossarySpelling::forSpelling('variant-a')->get();

        $this->assertEquals(1, $filtered->count());
        $this->assertEquals($spelling1->id, $filtered->first()->id);
    }
}

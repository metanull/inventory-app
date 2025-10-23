<?php

namespace Tests\Unit\Glossary;

use App\Models\Glossary;
use App\Models\GlossarySpelling;
use App\Models\GlossaryTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_valid_glossary(): void
    {
        $glossary = Glossary::factory()->create();

        $this->assertInstanceOf(Glossary::class, $glossary);
        $this->assertNotNull($glossary->id);
        $this->assertNotNull($glossary->internal_name);
        $this->assertIsString($glossary->internal_name);
    }

    public function test_factory_creates_glossary_in_database(): void
    {
        $glossary = Glossary::factory()->create([
            'internal_name' => 'test-glossary-term',
            'backward_compatibility' => 'legacy-id-123',
        ]);

        $this->assertDatabaseHas('glossaries', [
            'id' => $glossary->id,
            'internal_name' => 'test-glossary-term',
            'backward_compatibility' => 'legacy-id-123',
        ]);
    }

    public function test_glossary_has_translations_relationship(): void
    {
        $glossary = Glossary::factory()->create();
        $translation = GlossaryTranslation::factory()->for($glossary)->create();

        $this->assertTrue($glossary->translations->contains($translation));
        $this->assertEquals(1, $glossary->translations->count());
    }

    public function test_glossary_has_spellings_relationship(): void
    {
        $glossary = Glossary::factory()->create();
        $spelling = GlossarySpelling::factory()->for($glossary)->create();

        $this->assertTrue($glossary->spellings->contains($spelling));
        $this->assertEquals(1, $glossary->spellings->count());
    }

    public function test_glossary_has_synonyms_relationship(): void
    {
        $glossary1 = Glossary::factory()->create(['internal_name' => 'term-1']);
        $glossary2 = Glossary::factory()->create(['internal_name' => 'term-2']);

        $glossary1->synonyms()->attach($glossary2->id);

        $this->assertTrue($glossary1->synonyms->contains($glossary2));
        $this->assertEquals(1, $glossary1->synonyms->count());
    }

    public function test_glossary_uses_uuid_for_primary_key(): void
    {
        $glossary = Glossary::factory()->create();

        $this->assertIsString($glossary->id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $glossary->id
        );
    }
}

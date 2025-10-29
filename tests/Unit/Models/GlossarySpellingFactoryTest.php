<?php

namespace Tests\Unit\Models;

use App\Models\GlossarySpelling;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for GlossarySpelling factory.
 */
class GlossarySpellingFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_valid_glossary_spelling(): void
    {
        $spelling = GlossarySpelling::factory()->create();

        $this->assertInstanceOf(GlossarySpelling::class, $spelling);
        $this->assertNotEmpty($spelling->id);
        $this->assertNotEmpty($spelling->glossary_id);
        $this->assertNotEmpty($spelling->language_id);
        $this->assertNotEmpty($spelling->spelling);
    }
}

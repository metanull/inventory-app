<?php

namespace Tests\Unit\Models;

use App\Models\GlossarySpelling;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for GlossarySpelling model scopes.
 */
class GlossarySpellingScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_for_language_returns_spellings_for_specific_language(): void
    {
        $language1 = Language::factory()->create();
        $language2 = Language::factory()->create();

        $spelling1 = GlossarySpelling::factory()->create(['language_id' => $language1->id]);
        $spelling2 = GlossarySpelling::factory()->create(['language_id' => $language1->id]);
        $spelling3 = GlossarySpelling::factory()->create(['language_id' => $language2->id]);

        $results = GlossarySpelling::forLanguage($language1->id)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $spelling1->id));
        $this->assertTrue($results->contains('id', $spelling2->id));
        $this->assertFalse($results->contains('id', $spelling3->id));
    }

    public function test_scope_for_spelling_returns_exact_spelling_matches(): void
    {
        $spelling1 = GlossarySpelling::factory()->create(['spelling' => 'pottery']);
        $spelling2 = GlossarySpelling::factory()->create(['spelling' => 'pottery']);
        $spelling3 = GlossarySpelling::factory()->create(['spelling' => 'ceramic']);

        $results = GlossarySpelling::forSpelling('pottery')->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $spelling1->id));
        $this->assertTrue($results->contains('id', $spelling2->id));
        $this->assertFalse($results->contains('id', $spelling3->id));
    }
}

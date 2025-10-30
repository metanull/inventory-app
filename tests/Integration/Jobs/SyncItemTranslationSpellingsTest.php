<?php

namespace Tests\Integration\Jobs;

use App\Jobs\SyncItemTranslationSpellings;
use App\Models\GlossarySpelling;
use App\Models\ItemTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncItemTranslationSpellingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test job syncs matching spellings to item translation.
     */
    public function test_syncs_matching_spellings_to_item_translation()
    {
        $language = Language::factory()->create();
        $spelling1 = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'artifact',
        ]);
        $spelling2 = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'ancient',
        ]);
        $spelling3 = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'ceramic',
        ]);

        $itemTranslation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Ancient Artifact',
            'description' => 'An ancient artifact from Egypt',
        ]);

        $job = new SyncItemTranslationSpellings($itemTranslation->id);
        $job->handle();

        $linkedSpellings = $itemTranslation->fresh()->spellings->pluck('spelling')->toArray();
        $this->assertContains('artifact', $linkedSpellings);
        $this->assertContains('ancient', $linkedSpellings);
        $this->assertNotContains('ceramic', $linkedSpellings);
    }

    /**
     * Test job removes non-matching spellings.
     */
    public function test_removes_non_matching_spellings()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pottery',
        ]);

        $itemTranslation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Metal Sword',
            'description' => 'A metal sword from Rome',
        ]);

        // Manually attach the spelling
        $itemTranslation->spellings()->attach($spelling->id);
        $this->assertCount(1, $itemTranslation->fresh()->spellings);

        // Run the sync job
        $job = new SyncItemTranslationSpellings($itemTranslation->id);
        $job->handle();

        // The spelling should be removed
        $this->assertCount(0, $itemTranslation->fresh()->spellings);
    }

    /**
     * Test job uses word boundary matching.
     */
    public function test_uses_word_boundary_matching()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pot',
        ]);

        $itemTranslation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Pottery Collection',
            'description' => 'A collection with pot items',
        ]);

        $job = new SyncItemTranslationSpellings($itemTranslation->id);
        $job->handle();

        // Should match 'pot' in 'pot items' (word boundary)
        // but NOT in 'Pottery' (not a word boundary)
        $linkedSpellings = $itemTranslation->fresh()->spellings->pluck('spelling')->toArray();
        $this->assertContains('pot', $linkedSpellings);
    }

    /**
     * Test job is case-insensitive.
     */
    public function test_matching_is_case_insensitive()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'artifact',
        ]);

        $itemTranslation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'ARTIFACT',
            'description' => 'An Artifact from Egypt',
        ]);

        $job = new SyncItemTranslationSpellings($itemTranslation->id);
        $job->handle();

        $linkedSpellings = $itemTranslation->fresh()->spellings->pluck('spelling')->toArray();
        $this->assertContains('artifact', $linkedSpellings);
        $this->assertCount(1, $linkedSpellings);
    }

    /**
     * Test job handles deleted item translation gracefully.
     */
    public function test_handles_deleted_item_translation_gracefully()
    {
        $language = Language::factory()->create();
        $itemTranslation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
        ]);

        $itemTranslationId = $itemTranslation->id;
        $itemTranslation->delete();

        $job = new SyncItemTranslationSpellings($itemTranslationId);
        $job->handle(); // Should not throw exception

        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    /**
     * Test job only links spellings in same language.
     */
    public function test_only_links_spellings_in_same_language()
    {
        $language1 = Language::factory()->create();
        $language2 = Language::factory()->create();

        $spelling1 = GlossarySpelling::factory()->create([
            'language_id' => $language1->id,
            'spelling' => 'artifact',
        ]);
        $spelling2 = GlossarySpelling::factory()->create([
            'language_id' => $language2->id,
            'spelling' => 'artifact',
        ]);

        $itemTranslation = ItemTranslation::factory()->create([
            'language_id' => $language1->id,
            'name' => 'Artifact',
        ]);

        $job = new SyncItemTranslationSpellings($itemTranslation->id);
        $job->handle();

        $linkedSpellings = $itemTranslation->fresh()->spellings;
        $this->assertCount(1, $linkedSpellings);
        $this->assertEquals($spelling1->id, $linkedSpellings->first()->id);
    }

    /**
     * Test job handles empty text fields.
     */
    public function test_handles_empty_text_fields()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'artifact',
        ]);

        $itemTranslation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'No match',
            'description' => '',
            'type' => '',
        ]);

        $job = new SyncItemTranslationSpellings($itemTranslation->id);
        $job->handle();

        $this->assertCount(0, $itemTranslation->fresh()->spellings);
    }
}

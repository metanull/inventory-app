<?php

namespace Tests\Api\Relationships;

use App\Models\Glossary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Api\Traits\AuthenticatesApiRequests;
use Tests\TestCase;

class GlossarySynonymAttachTest extends TestCase
{
    use AuthenticatesApiRequests, RefreshDatabase;

    // #region ATTACH TESTS

    public function test_glossary_synonym_attach_can_attach_synonym_to_glossary(): void
    {
        $glossary = Glossary::factory()->create();
        $synonym = Glossary::factory()->create();

        $response = $this->postJson(route('glossary.attachSynonym', $glossary->id), [
            'synonym_id' => $synonym->id,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                ],
            ]);

        // Verify synonym was attached in database
        $this->assertTrue($glossary->fresh()->synonyms()->where('synonym_id', $synonym->id)->exists());
    }

    public function test_glossary_synonym_attach_does_not_create_duplicate_attachment(): void
    {
        $glossary = Glossary::factory()->create();
        $synonym = Glossary::factory()->create();

        // First attach
        $glossary->synonyms()->attach($synonym);

        // Try to attach again
        $response = $this->postJson(route('glossary.attachSynonym', $glossary->id), [
            'synonym_id' => $synonym->id,
        ]);

        $response->assertOk();

        // Should still have only one attachment
        $this->assertCount(1, $glossary->fresh()->synonyms);
    }

    public function test_glossary_synonym_attach_prevents_self_reference(): void
    {
        $glossary = Glossary::factory()->create();

        $response = $this->postJson(route('glossary.attachSynonym', $glossary->id), [
            'synonym_id' => $glossary->id,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'A glossary entry cannot be a synonym of itself.',
            ]);
    }

    public function test_glossary_synonym_attach_validation_requires_synonym_id(): void
    {
        $glossary = Glossary::factory()->create();

        $response = $this->postJson(route('glossary.attachSynonym', $glossary->id), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['synonym_id']);
    }

    public function test_glossary_synonym_attach_validation_requires_valid_uuid(): void
    {
        $glossary = Glossary::factory()->create();

        $response = $this->postJson(route('glossary.attachSynonym', $glossary->id), [
            'synonym_id' => 'invalid-uuid',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['synonym_id']);
    }

    public function test_glossary_synonym_attach_validation_requires_existing_glossary(): void
    {
        $glossary = Glossary::factory()->create();
        $nonExistentUuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';

        $response = $this->postJson(route('glossary.attachSynonym', $glossary->id), [
            'synonym_id' => $nonExistentUuid,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['synonym_id']);
    }

    public function test_glossary_synonym_attach_returns_glossary_with_includes(): void
    {
        $glossary = Glossary::factory()->create();
        $synonym = Glossary::factory()->create();

        $response = $this->postJson(route('glossary.attachSynonym', [$glossary->id, 'include' => 'synonyms']), [
            'synonym_id' => $synonym->id,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                    'synonyms' => [
                        '*' => [
                            'id',
                            'internal_name',
                        ],
                    ],
                ],
            ]);
    }

    public function test_glossary_synonym_attach_returns_not_found_for_nonexistent_glossary(): void
    {
        $synonym = Glossary::factory()->create();

        $response = $this->postJson(route('glossary.attachSynonym', 'nonexistent-uuid'), [
            'synonym_id' => $synonym->id,
        ]);

        $response->assertNotFound();
    }

    // #endregion

    // #region DETACH TESTS

    public function test_glossary_synonym_detach_can_detach_synonym_from_glossary(): void
    {
        $glossary = Glossary::factory()->create();
        $synonym = Glossary::factory()->create();

        // First attach the synonym
        $glossary->synonyms()->attach($synonym);

        $response = $this->deleteJson(route('glossary.detachSynonym', $glossary->id), [
            'synonym_id' => $synonym->id,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                ],
            ]);

        // Verify synonym was detached in database
        $this->assertFalse($glossary->fresh()->synonyms()->where('synonym_id', $synonym->id)->exists());
    }

    public function test_glossary_synonym_detach_detaching_non_attached_synonym_does_not_cause_error(): void
    {
        $glossary = Glossary::factory()->create();
        $synonym = Glossary::factory()->create();

        // Synonym is not attached

        $response = $this->deleteJson(route('glossary.detachSynonym', $glossary->id), [
            'synonym_id' => $synonym->id,
        ]);

        $response->assertOk();
        $this->assertCount(0, $glossary->fresh()->synonyms);
    }

    public function test_glossary_synonym_detach_validation_requires_synonym_id(): void
    {
        $glossary = Glossary::factory()->create();

        $response = $this->deleteJson(route('glossary.detachSynonym', $glossary->id), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['synonym_id']);
    }

    public function test_glossary_synonym_detach_validation_requires_valid_uuid(): void
    {
        $glossary = Glossary::factory()->create();

        $response = $this->deleteJson(route('glossary.detachSynonym', $glossary->id), [
            'synonym_id' => 'invalid-uuid',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['synonym_id']);
    }

    public function test_glossary_synonym_detach_validation_requires_existing_glossary(): void
    {
        $glossary = Glossary::factory()->create();
        $nonExistentUuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';

        $response = $this->deleteJson(route('glossary.detachSynonym', $glossary->id), [
            'synonym_id' => $nonExistentUuid,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['synonym_id']);
    }

    public function test_glossary_synonym_detach_returns_glossary_with_includes(): void
    {
        $glossary = Glossary::factory()->create();
        $synonym = Glossary::factory()->create();
        $glossary->synonyms()->attach($synonym);

        $response = $this->deleteJson(route('glossary.detachSynonym', [$glossary->id, 'include' => 'synonyms']), [
            'synonym_id' => $synonym->id,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                    'synonyms',
                ],
            ]);
    }

    public function test_glossary_synonym_detach_returns_not_found_for_nonexistent_glossary(): void
    {
        $synonym = Glossary::factory()->create();

        $response = $this->deleteJson(route('glossary.detachSynonym', 'nonexistent-uuid'), [
            'synonym_id' => $synonym->id,
        ]);

        $response->assertNotFound();
    }

    // #endregion
}

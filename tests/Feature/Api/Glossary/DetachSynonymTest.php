<?php

namespace Tests\Feature\Api\Glossary;

use App\Models\Glossary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DetachSynonymTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_can_detach_synonym_from_glossary(): void
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

    public function test_detaching_non_attached_synonym_does_not_cause_error(): void
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

    public function test_validation_requires_synonym_id(): void
    {
        $glossary = Glossary::factory()->create();

        $response = $this->deleteJson(route('glossary.detachSynonym', $glossary->id), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['synonym_id']);
    }

    public function test_validation_requires_valid_uuid(): void
    {
        $glossary = Glossary::factory()->create();

        $response = $this->deleteJson(route('glossary.detachSynonym', $glossary->id), [
            'synonym_id' => 'invalid-uuid',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['synonym_id']);
    }

    public function test_validation_requires_existing_glossary(): void
    {
        $glossary = Glossary::factory()->create();
        $nonExistentUuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';

        $response = $this->deleteJson(route('glossary.detachSynonym', $glossary->id), [
            'synonym_id' => $nonExistentUuid,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['synonym_id']);
    }

    public function test_returns_glossary_with_includes(): void
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

    public function test_returns_not_found_for_nonexistent_glossary(): void
    {
        $synonym = Glossary::factory()->create();

        $response = $this->deleteJson(route('glossary.detachSynonym', 'nonexistent-uuid'), [
            'synonym_id' => $synonym->id,
        ]);

        $response->assertNotFound();
    }
}

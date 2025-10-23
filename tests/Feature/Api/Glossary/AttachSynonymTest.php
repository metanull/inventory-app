<?php

namespace Tests\Feature\Api\Glossary;

use App\Models\Glossary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class AttachSynonymTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;
    use WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_can_attach_synonym_to_glossary(): void
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

    public function test_does_not_create_duplicate_attachment(): void
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

    public function test_prevents_self_reference(): void
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

    public function test_validation_requires_synonym_id(): void
    {
        $glossary = Glossary::factory()->create();

        $response = $this->postJson(route('glossary.attachSynonym', $glossary->id), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['synonym_id']);
    }

    public function test_validation_requires_valid_uuid(): void
    {
        $glossary = Glossary::factory()->create();

        $response = $this->postJson(route('glossary.attachSynonym', $glossary->id), [
            'synonym_id' => 'invalid-uuid',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['synonym_id']);
    }

    public function test_validation_requires_existing_glossary(): void
    {
        $glossary = Glossary::factory()->create();
        $nonExistentUuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';

        $response = $this->postJson(route('glossary.attachSynonym', $glossary->id), [
            'synonym_id' => $nonExistentUuid,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['synonym_id']);
    }

    public function test_returns_glossary_with_includes(): void
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

    public function test_returns_not_found_for_nonexistent_glossary(): void
    {
        $synonym = Glossary::factory()->create();

        $response = $this->postJson(route('glossary.attachSynonym', 'nonexistent-uuid'), [
            'synonym_id' => $synonym->id,
        ]);

        $response->assertNotFound();
    }
}

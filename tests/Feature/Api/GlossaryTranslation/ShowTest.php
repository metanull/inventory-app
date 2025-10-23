<?php

namespace Tests\Feature\Api\GlossaryTranslation;

use App\Models\GlossaryTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class ShowTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user, 'sanctum');
    }

    /**
     * Authentication: show allows authenticated users.
     */
    public function test_show_allows_authenticated_users()
    {
        $translation = GlossaryTranslation::factory()->create();

        $response = $this->getJson(route('glossary-translation.show', $translation));
        $response->assertOk();
    }

    /**
     * Process: show returns the requested translation.
     */
    public function test_show_returns_the_requested_translation()
    {
        $translation = GlossaryTranslation::factory()->create();

        $response = $this->getJson(route('glossary-translation.show', $translation));
        $response->assertOk();
        $response->assertJsonPath('data.id', $translation->id);
        $response->assertJsonPath('data.glossary_id', $translation->glossary_id);
        $response->assertJsonPath('data.definition', $translation->definition);
    }

    /**
     * Response: show returns the expected structure.
     */
    public function test_show_returns_the_expected_structure()
    {
        $translation = GlossaryTranslation::factory()->create();

        $response = $this->getJson(route('glossary-translation.show', $translation));
        $response->assertJsonStructure([
            'data' => [
                'id',
                'glossary_id',
                'language_id',
                'definition',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    /**
     * Error: show returns 404 for non-existent translation.
     */
    public function test_show_returns_404_for_non_existent_translation()
    {
        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $response = $this->getJson(route('glossary-translation.show', $nonExistentId));
        $response->assertNotFound();
    }
}

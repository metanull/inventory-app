<?php

namespace Tests\Feature\Api\GlossarySpelling;

use App\Models\GlossarySpelling;
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
        $spelling = GlossarySpelling::factory()->create();

        $response = $this->getJson(route('glossary-spelling.show', $spelling));
        $response->assertOk();
    }

    /**
     * Process: show returns the requested spelling.
     */
    public function test_show_returns_the_requested_spelling()
    {
        $spelling = GlossarySpelling::factory()->create();

        $response = $this->getJson(route('glossary-spelling.show', $spelling));
        $response->assertOk();
        $response->assertJsonPath('data.id', $spelling->id);
        $response->assertJsonPath('data.glossary_id', $spelling->glossary_id);
        $response->assertJsonPath('data.spelling', $spelling->spelling);
    }

    /**
     * Response: show returns the expected structure.
     */
    public function test_show_returns_the_expected_structure()
    {
        $spelling = GlossarySpelling::factory()->create();

        $response = $this->getJson(route('glossary-spelling.show', $spelling));
        $response->assertJsonStructure([
            'data' => [
                'id',
                'glossary_id',
                'language_id',
                'spelling',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    /**
     * Error: show returns 404 for non-existent spelling.
     */
    public function test_show_returns_404_for_non_existent_spelling()
    {
        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $response = $this->getJson(route('glossary-spelling.show', $nonExistentId));
        $response->assertNotFound();
    }
}

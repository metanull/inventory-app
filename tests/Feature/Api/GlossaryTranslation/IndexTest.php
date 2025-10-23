<?php

namespace Tests\Feature\Api\GlossaryTranslation;

use App\Models\GlossaryTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class IndexTest extends TestCase
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
     * Authentication: index allows authenticated users.
     */
    public function test_index_allows_authenticated_users()
    {
        $response = $this->getJson(route('glossary-translation.index'));
        $response->assertOk();
    }

    /**
     * Process: index returns all translations.
     */
    public function test_index_returns_all_translations()
    {
        $translations = GlossaryTranslation::factory()->count(3)->create();

        $response = $this->getJson(route('glossary-translation.index'));
        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    /**
     * Response: index returns the expected structure.
     */
    public function test_index_returns_the_expected_structure()
    {
        GlossaryTranslation::factory()->create();

        $response = $this->getJson(route('glossary-translation.index'));
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'glossary_id',
                    'language_id',
                    'definition',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    /**
     * Pagination: index supports pagination.
     */
    public function test_index_supports_pagination()
    {
        GlossaryTranslation::factory()->count(15)->create();

        $response = $this->getJson(route('glossary-translation.index', ['per_page' => 5]));
        $response->assertOk();
        $response->assertJsonCount(5, 'data');
    }
}

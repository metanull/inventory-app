<?php

namespace Tests\Feature\Api\Glossary;

use App\Models\Glossary;
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
        $glossary = Glossary::factory()->create();

        $response = $this->getJson(route('glossary.show', $glossary));
        $response->assertOk();
    }

    /**
     * Process: show returns the requested glossary.
     */
    public function test_show_returns_the_requested_glossary()
    {
        $glossary = Glossary::factory()->create();

        $response = $this->getJson(route('glossary.show', $glossary));
        $response->assertOk();
        $response->assertJsonPath('data.id', $glossary->id);
        $response->assertJsonPath('data.internal_name', $glossary->internal_name);
    }

    /**
     * Response: show returns the expected structure.
     */
    public function test_show_returns_the_expected_structure()
    {
        $glossary = Glossary::factory()->create();

        $response = $this->getJson(route('glossary.show', $glossary));
        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'backward_compatibility',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    /**
     * Error: show returns 404 for non-existent glossary.
     */
    public function test_show_returns_404_for_non_existent_glossary()
    {
        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $response = $this->getJson(route('glossary.show', $nonExistentId));
        $response->assertNotFound();
    }
}

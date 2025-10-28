<?php

namespace Tests\Feature\Api\Glossary;

use App\Enums\Permission;
use App\Models\Glossary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class IndexTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith([Permission::VIEW_DATA->value]);
        $this->actingAs($this->user, 'sanctum');
    }

    /**
     * Authentication: index allows authenticated users.
     */
    public function test_index_allows_authenticated_users()
    {
        $response = $this->getJson(route('glossary.index'));
        $response->assertOk();
    }

    /**
     * Process: index returns all glossaries.
     */
    public function test_index_returns_all_glossaries()
    {
        $glossaries = Glossary::factory()->count(3)->create();

        $response = $this->getJson(route('glossary.index'));
        $response->assertOk();
        $response->assertJsonCount(3, 'data');
    }

    /**
     * Response: index returns the expected structure.
     */
    public function test_index_returns_the_expected_structure()
    {
        Glossary::factory()->create();

        $response = $this->getJson(route('glossary.index'));
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'internal_name',
                    'backward_compatibility',
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
        Glossary::factory()->count(15)->create();

        $response = $this->getJson(route('glossary.index', ['per_page' => 5]));
        $response->assertOk();
        $response->assertJsonCount(5, 'data');
    }
}

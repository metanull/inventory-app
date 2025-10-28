<?php

namespace Tests\Feature\Api\Glossary;

use App\Models\Glossary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class UpdateTest extends TestCase
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
     * Authentication: update allows authenticated users.
     */
    public function test_update_allows_authenticated_users()
    {
        $glossary = Glossary::factory()->create();
        $data = ['internal_name' => 'Updated Name'];

        $response = $this->patchJson(route('glossary.update', $glossary), $data);
        $response->assertOk();
    }

    /**
     * Process: update modifies the glossary.
     */
    public function test_update_modifies_the_glossary()
    {
        $glossary = Glossary::factory()->create();
        $data = ['internal_name' => 'Updated Name', 'backward_compatibility' => 'UPD'];

        $response = $this->patchJson(route('glossary.update', $glossary), $data);
        $response->assertOk();

        $glossary->refresh();
        $this->assertEquals('Updated Name', $glossary->internal_name);
        $this->assertEquals('UPD', $glossary->backward_compatibility);
    }

    /**
     * Response: update returns the expected structure.
     */
    public function test_update_returns_the_expected_structure()
    {
        $glossary = Glossary::factory()->create();
        $data = ['internal_name' => 'Updated Name'];

        $response = $this->patchJson(route('glossary.update', $glossary), $data);
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
     * Validation: update enforces unique internal_name.
     */
    public function test_update_enforces_unique_internal_name()
    {
        $existing = Glossary::factory()->create();
        $glossary = Glossary::factory()->create();
        $data = ['internal_name' => $existing->internal_name];

        $response = $this->patchJson(route('glossary.update', $glossary), $data);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }
}

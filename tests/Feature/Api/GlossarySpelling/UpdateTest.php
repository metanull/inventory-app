<?php

namespace Tests\Feature\Api\GlossarySpelling;

use App\Models\GlossarySpelling;
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
        $spelling = GlossarySpelling::factory()->create();
        $data = ['spelling' => 'updated-spelling'];

        $response = $this->patchJson(route('glossary-spelling.update', $spelling), $data);
        $response->assertOk();
    }

    /**
     * Process: update modifies the spelling.
     */
    public function test_update_modifies_the_spelling()
    {
        $spelling = GlossarySpelling::factory()->create();
        $newSpelling = 'updated-spelling-text';
        $data = [
            'glossary_id' => $spelling->glossary_id,
            'language_id' => $spelling->language_id,
            'spelling' => $newSpelling,
        ];

        $response = $this->patchJson(route('glossary-spelling.update', $spelling), $data);
        $response->assertOk();

        $spelling->refresh();
        $this->assertEquals($newSpelling, $spelling->spelling);
    }

    /**
     * Response: update returns the expected structure.
     */
    public function test_update_returns_the_expected_structure()
    {
        $spelling = GlossarySpelling::factory()->create();
        $data = ['spelling' => 'updated', 'glossary_id' => $spelling->glossary_id, 'language_id' => $spelling->language_id];

        $response = $this->patchJson(route('glossary-spelling.update', $spelling), $data);
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
     * Validation: update validates required fields.
     */
    public function test_update_validates_required_fields()
    {
        $spelling = GlossarySpelling::factory()->create();
        $data = ['spelling' => ''];

        $response = $this->patchJson(route('glossary-spelling.update', $spelling), $data);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['spelling']);
    }
}

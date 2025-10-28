<?php

namespace Tests\Feature\Api\GlossaryTranslation;

use App\Enums\Permission;
use App\Models\GlossaryTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class UpdateTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith(Permission::dataOperations());
        $this->actingAs($this->user, 'sanctum');
    }

    /**
     * Authentication: update allows authenticated users.
     */
    public function test_update_allows_authenticated_users()
    {
        $translation = GlossaryTranslation::factory()->create();
        $data = ['definition' => 'Updated definition'];

        $response = $this->patchJson(route('glossary-translation.update', $translation), $data);
        $response->assertOk();
    }

    /**
     * Process: update modifies the translation.
     */
    public function test_update_modifies_the_translation()
    {
        $translation = GlossaryTranslation::factory()->create();
        $newDefinition = 'Updated definition text';
        $data = [
            'glossary_id' => $translation->glossary_id,
            'language_id' => $translation->language_id,
            'definition' => $newDefinition,
        ];

        $response = $this->patchJson(route('glossary-translation.update', $translation), $data);
        $response->assertOk();

        $translation->refresh();
        $this->assertEquals($newDefinition, $translation->definition);
    }

    /**
     * Response: update returns the expected structure.
     */
    public function test_update_returns_the_expected_structure()
    {
        $translation = GlossaryTranslation::factory()->create();
        $data = ['definition' => 'Updated definition', 'glossary_id' => $translation->glossary_id, 'language_id' => $translation->language_id];

        $response = $this->patchJson(route('glossary-translation.update', $translation), $data);
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
     * Validation: update validates required fields.
     */
    public function test_update_validates_required_fields()
    {
        $translation = GlossaryTranslation::factory()->create();
        $data = ['definition' => ''];

        $response = $this->patchJson(route('glossary-translation.update', $translation), $data);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['definition']);
    }
}

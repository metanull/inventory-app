<?php

namespace Tests\Feature\Api\GlossaryTranslation;

use App\Models\Glossary;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class StoreTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user, 'sanctum');
    }

    /**
     * Authentication: store allows authenticated users.
     */
    public function test_store_allows_authenticated_users()
    {
        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();
        $data = [
            'glossary_id' => $glossary->id,
            'language_id' => $language->id,
            'definition' => $this->faker->paragraph(),
        ];

        $response = $this->postJson(route('glossary-translation.store'), $data);
        $response->assertCreated();
    }

    /**
     * Process: store creates a row.
     */
    public function test_store_creates_a_row()
    {
        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();
        $data = [
            'glossary_id' => $glossary->id,
            'language_id' => $language->id,
            'definition' => $this->faker->paragraph(),
        ];

        $response = $this->postJson(route('glossary-translation.store'), $data);
        $response->assertCreated();
        $this->assertDatabaseHas('glossary_translations', [
            'glossary_id' => $data['glossary_id'],
            'language_id' => $data['language_id'],
        ]);
    }

    /**
     * Response: store returns created on success.
     */
    public function test_store_returns_created_on_success()
    {
        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();
        $data = [
            'glossary_id' => $glossary->id,
            'language_id' => $language->id,
            'definition' => $this->faker->paragraph(),
        ];

        $response = $this->postJson(route('glossary-translation.store'), $data);
        $response->assertCreated();
    }

    /**
     * Response: store returns unprocessable entity when input is invalid.
     */
    public function test_store_returns_unprocessable_entity_when_input_is_invalid()
    {
        $data = [
            'id' => $this->faker->uuid(),
        ];

        $response = $this->postJson(route('glossary-translation.store'), $data);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['glossary_id', 'language_id', 'definition', 'id']);
    }

    /**
     * Response: store returns the expected structure.
     */
    public function test_store_returns_the_expected_structure()
    {
        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();
        $data = [
            'glossary_id' => $glossary->id,
            'language_id' => $language->id,
            'definition' => $this->faker->paragraph(),
        ];

        $response = $this->postJson(route('glossary-translation.store'), $data);
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
     * Response: store returns the expected data.
     */
    public function test_store_returns_the_expected_data()
    {
        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();
        $data = [
            'glossary_id' => $glossary->id,
            'language_id' => $language->id,
            'definition' => $this->faker->paragraph(),
        ];

        $response = $this->postJson(route('glossary-translation.store'), $data);
        $response->assertJsonPath('data.glossary_id', $data['glossary_id']);
        $response->assertJsonPath('data.language_id', $data['language_id']);
        $response->assertJsonPath('data.definition', $data['definition']);
    }

    /**
     * Validation: store validates required fields.
     */
    public function test_store_validates_required_fields()
    {
        $data = [];

        $response = $this->postJson(route('glossary-translation.store'), $data);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['glossary_id', 'language_id', 'definition']);
    }

    /**
     * Validation: store validates glossary_id exists.
     */
    public function test_store_validates_glossary_id_exists()
    {
        $language = Language::factory()->create();
        $data = [
            'glossary_id' => $this->faker->uuid(),
            'language_id' => $language->id,
            'definition' => $this->faker->paragraph(),
        ];

        $response = $this->postJson(route('glossary-translation.store'), $data);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['glossary_id']);
    }

    /**
     * Validation: store validates language_id exists.
     */
    public function test_store_validates_language_id_exists()
    {
        $glossary = Glossary::factory()->create();
        $data = [
            'glossary_id' => $glossary->id,
            'language_id' => 'zzz',
            'definition' => $this->faker->paragraph(),
        ];

        $response = $this->postJson(route('glossary-translation.store'), $data);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }
}

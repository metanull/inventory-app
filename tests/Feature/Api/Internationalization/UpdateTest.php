<?php

namespace Tests\Feature\Api\Internationalization;

use App\Models\Author;
use App\Models\Internationalization;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_authenticated_user_can_update_internationalization(): void
    {
        $internationalization = Internationalization::factory()->create();
        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated Description',
            'type' => 'Updated Type',
        ];

        $response = $this->putJson(route('internationalization.update', $internationalization->id), $updateData);

        $response->assertOk()
            ->assertJsonPath('data.name', $updateData['name'])
            ->assertJsonPath('data.description', $updateData['description'])
            ->assertJsonPath('data.type', $updateData['type']);

        $this->assertDatabaseHas('internationalizations', [
            'id' => $internationalization->id,
            'name' => $updateData['name'],
            'description' => $updateData['description'],
            'type' => $updateData['type'],
        ]);
    }

    public function test_update_can_modify_all_optional_fields(): void
    {
        $internationalization = Internationalization::factory()->create();
        $author = Author::factory()->create();
        $updateData = [
            'alternate_name' => 'New Alternate Name',
            'type' => 'New Type',
            'holder' => 'New Holder',
            'owner' => 'New Owner',
            'initial_owner' => 'New Initial Owner',
            'dates' => 'New Dates',
            'location' => 'New Location',
            'dimensions' => 'New Dimensions',
            'place_of_production' => 'New Place',
            'method_for_datation' => 'New Datation Method',
            'method_for_provenance' => 'New Provenance Method',
            'obtention' => 'New Obtention',
            'bibliography' => 'New Bibliography',
            'extra' => ['new_field' => 'new_value'],
            'author_id' => $author->id,
            'text_copy_editor_id' => $author->id,
            'translator_id' => $author->id,
            'translation_copy_editor_id' => $author->id,
            'backward_compatibility' => 'new-compatibility-id',
        ];

        $response = $this->putJson(route('internationalization.update', $internationalization->id), $updateData);

        $response->assertOk();
        $this->assertDatabaseHas('internationalizations', [
            'id' => $internationalization->id,
            'alternate_name' => $updateData['alternate_name'],
            'type' => $updateData['type'],
            'holder' => $updateData['holder'],
            'author_id' => $updateData['author_id'],
        ]);
    }

    public function test_update_can_change_language_if_combination_remains_unique(): void
    {
        $internationalization = Internationalization::factory()->create();
        $newLanguage = Language::factory()->create();

        $updateData = [
            'language_id' => $newLanguage->id,
        ];

        $response = $this->putJson(route('internationalization.update', $internationalization->id), $updateData);

        $response->assertOk()
            ->assertJsonPath('data.language_id', $newLanguage->id);

        $this->assertDatabaseHas('internationalizations', [
            'id' => $internationalization->id,
            'language_id' => $newLanguage->id,
        ]);
    }

    public function test_update_validates_unique_constraint_on_language_contextualization_combination(): void
    {
        $internationalization1 = Internationalization::factory()->create();
        $internationalization2 = Internationalization::factory()->create();

        // Try to update internationalization2 to have the same language and contextualization as internationalization1
        $updateData = [
            'contextualization_id' => $internationalization1->contextualization_id,
            'language_id' => $internationalization1->language_id,
        ];

        $response = $this->putJson(route('internationalization.update', $internationalization2->id), $updateData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['language_id']);
    }

    public function test_update_validates_language_id_exists(): void
    {
        $internationalization = Internationalization::factory()->create();
        $updateData = [
            'language_id' => 'xxx',
        ];

        $response = $this->putJson(route('internationalization.update', $internationalization->id), $updateData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['language_id']);
    }

    public function test_update_validates_language_id_length(): void
    {
        $internationalization = Internationalization::factory()->create();
        $updateData = [
            'language_id' => 'en',
        ];

        $response = $this->putJson(route('internationalization.update', $internationalization->id), $updateData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['language_id']);
    }

    public function test_update_validates_author_id_exists(): void
    {
        $internationalization = Internationalization::factory()->create();
        $updateData = [
            'author_id' => $this->faker->uuid(),
        ];

        $response = $this->putJson(route('internationalization.update', $internationalization->id), $updateData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['author_id']);
    }

    public function test_update_prohibits_id_field(): void
    {
        $internationalization = Internationalization::factory()->create();
        $updateData = [
            'id' => $this->faker->uuid(),
            'name' => 'Updated Name',
        ];

        $response = $this->putJson(route('internationalization.update', $internationalization->id), $updateData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['id']);
    }

    public function test_update_returns_404_for_nonexistent_internationalization(): void
    {
        $nonExistentId = $this->faker->uuid();
        $updateData = [
            'name' => 'Updated Name',
        ];

        $response = $this->putJson(route('internationalization.update', $nonExistentId), $updateData);

        $response->assertNotFound();
    }

    public function test_update_converts_empty_strings_to_null(): void
    {
        $internationalization = Internationalization::factory()->create([
            'alternate_name' => 'Original Name',
            'type' => 'Original Type',
        ]);

        $updateData = [
            'alternate_name' => '',
            'type' => '',
        ];

        $response = $this->putJson(route('internationalization.update', $internationalization->id), $updateData);

        $response->assertOk();
        $internationalization->refresh();
        $this->assertNull($internationalization->alternate_name);
        $this->assertNull($internationalization->type);
    }

    public function test_update_can_set_nullable_fields_to_null(): void
    {
        $internationalization = Internationalization::factory()->create([
            'alternate_name' => 'Original Name',
            'type' => 'Original Type',
            'holder' => 'Original Holder',
        ]);

        $updateData = [
            'alternate_name' => null,
            'type' => null,
            'holder' => null,
        ];

        $response = $this->putJson(route('internationalization.update', $internationalization->id), $updateData);

        $response->assertOk();
        $internationalization->refresh();
        $this->assertNull($internationalization->alternate_name);
        $this->assertNull($internationalization->type);
        $this->assertNull($internationalization->holder);
    }

    public function test_update_preserves_unchanged_fields(): void
    {
        $internationalization = Internationalization::factory()->create();
        $originalName = $internationalization->name;
        $originalDescription = $internationalization->description;

        $updateData = [
            'type' => 'Updated Type',
        ];

        $response = $this->putJson(route('internationalization.update', $internationalization->id), $updateData);

        $response->assertOk()
            ->assertJsonPath('data.name', $originalName)
            ->assertJsonPath('data.description', $originalDescription)
            ->assertJsonPath('data.type', $updateData['type']);
    }
}

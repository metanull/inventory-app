<?php

namespace Tests\Feature\Api\DetailTranslation;

use App\Models\Author;
use App\Models\Context;
use App\Models\Detail;
use App\Models\DetailTranslation;
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

    public function test_can_update_detail_translation(): void
    {
        $translation = DetailTranslation::factory()->create();
        $data = [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph,
            'alternate_name' => $this->faker->words(2, true),
        ];

        $response = $this->putJson(route('detail-translation.update', ['detail_translation' => $translation->id]), $data);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'detail_id',
                    'language_id',
                    'context_id',
                    'name',
                    'description',
                    'alternate_name',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('detail_translations', [
            'id' => $translation->id,
            'name' => $data['name'],
            'description' => $data['description'],
            'alternate_name' => $data['alternate_name'],
        ]);
    }

    public function test_update_can_modify_all_optional_fields(): void
    {
        $translation = DetailTranslation::factory()->create();
        $author = Author::factory()->create();

        $updateData = [
            'alternate_name' => 'New Alternate Name',
            'extra' => ['new_field' => 'new_value'],
            'author_id' => $author->id,
            'text_copy_editor_id' => $author->id,
            'translator_id' => $author->id,
            'translation_copy_editor_id' => $author->id,
            'backward_compatibility' => 'new-compatibility-id',
        ];

        // Convert extra field to JSON string for API request
        if (isset($updateData['extra']) && is_array($updateData['extra'])) {
            $updateData['extra'] = json_encode($updateData['extra']);
        }

        $response = $this->putJson(route('detail-translation.update', $translation->id), $updateData);

        $response->assertOk();
        $this->assertDatabaseHas('detail_translations', [
            'id' => $translation->id,
            'alternate_name' => $updateData['alternate_name'],
            'author_id' => $updateData['author_id'],
            'text_copy_editor_id' => $updateData['text_copy_editor_id'],
            'translator_id' => $updateData['translator_id'],
            'translation_copy_editor_id' => $updateData['translation_copy_editor_id'],
            'backward_compatibility' => $updateData['backward_compatibility'],
        ]);
    }

    public function test_update_can_change_detail_if_combination_remains_unique(): void
    {
        $translation = DetailTranslation::factory()->create();
        $newDetail = Detail::factory()->withoutTranslations()->create();

        $updateData = [
            'detail_id' => $newDetail->id,
        ];

        $response = $this->putJson(route('detail-translation.update', $translation->id), $updateData);

        $response->assertOk()
            ->assertJsonPath('data.detail_id', $newDetail->id);

        $this->assertDatabaseHas('detail_translations', [
            'id' => $translation->id,
            'detail_id' => $newDetail->id,
        ]);
    }

    public function test_update_can_change_language_if_combination_remains_unique(): void
    {
        $translation = DetailTranslation::factory()->create();
        $newLanguage = Language::factory()->create();

        $updateData = [
            'language_id' => $newLanguage->id,
        ];

        $response = $this->putJson(route('detail-translation.update', $translation->id), $updateData);

        $response->assertOk()
            ->assertJsonPath('data.language_id', $newLanguage->id);

        $this->assertDatabaseHas('detail_translations', [
            'id' => $translation->id,
            'language_id' => $newLanguage->id,
        ]);
    }

    public function test_update_can_change_context_if_combination_remains_unique(): void
    {
        $translation = DetailTranslation::factory()->create();
        $newContext = Context::factory()->create();

        $updateData = [
            'context_id' => $newContext->id,
        ];

        $response = $this->putJson(route('detail-translation.update', $translation->id), $updateData);

        $response->assertOk()
            ->assertJsonPath('data.context_id', $newContext->id);

        $this->assertDatabaseHas('detail_translations', [
            'id' => $translation->id,
            'context_id' => $newContext->id,
        ]);
    }

    public function test_update_validates_unique_constraint_on_detail_language_context_combination(): void
    {
        $translation1 = DetailTranslation::factory()->create();
        $translation2 = DetailTranslation::factory()->create();

        // Try to update translation2 to have the same detail, language, and context as translation1
        $updateData = [
            'detail_id' => $translation1->detail_id,
            'language_id' => $translation1->language_id,
            'context_id' => $translation1->context_id,
        ];

        $response = $this->putJson(route('detail-translation.update', $translation2->id), $updateData);

        $response->assertUnprocessable();
    }

    public function test_update_allows_null_values_for_optional_fields(): void
    {
        $translation = DetailTranslation::factory()->create([
            'alternate_name' => 'Original Name',
        ]);

        $updateData = [
            'alternate_name' => null,
        ];

        $response = $this->putJson(route('detail-translation.update', $translation->id), $updateData);

        $response->assertOk();
        $translation->refresh();
        $this->assertNull($translation->alternate_name);
    }

    public function test_update_preserves_unchanged_fields(): void
    {
        $translation = DetailTranslation::factory()->create();
        $originalName = $translation->name;
        $originalDescription = $translation->description;

        $updateData = [
            'alternate_name' => 'Updated Alternate Name',
        ];

        $response = $this->putJson(route('detail-translation.update', $translation->id), $updateData);

        $response->assertOk()
            ->assertJsonPath('data.name', $originalName)
            ->assertJsonPath('data.description', $originalDescription)
            ->assertJsonPath('data.alternate_name', $updateData['alternate_name']);
    }

    public function test_update_validates_detail_id_exists(): void
    {
        $translation = DetailTranslation::factory()->create();
        $updateData = [
            'detail_id' => 'non-existent-id',
        ];

        $response = $this->putJson(route('detail-translation.update', $translation->id), $updateData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['detail_id']);
    }

    public function test_update_validates_language_id_exists(): void
    {
        $translation = DetailTranslation::factory()->create();
        $updateData = [
            'language_id' => 'xxx',
        ];

        $response = $this->putJson(route('detail-translation.update', $translation->id), $updateData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['language_id']);
    }

    public function test_update_validates_context_id_exists(): void
    {
        $translation = DetailTranslation::factory()->create();
        $updateData = [
            'context_id' => 'non-existent-id',
        ];

        $response = $this->putJson(route('detail-translation.update', $translation->id), $updateData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['context_id']);
    }

    public function test_update_validates_author_id_exists(): void
    {
        $translation = DetailTranslation::factory()->create();
        $updateData = [
            'author_id' => 'non-existent-id',
        ];

        $response = $this->putJson(route('detail-translation.update', $translation->id), $updateData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['author_id']);
    }

    public function test_update_returns_not_found_for_non_existent_detail_translation(): void
    {
        $data = [
            'name' => $this->faker->words(3, true),
        ];

        $response = $this->putJson(route('detail-translation.update', ['detail_translation' => 'non-existent-id']), $data);

        $response->assertNotFound();
    }
}

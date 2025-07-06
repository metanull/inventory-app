<?php

namespace Tests\Feature\Api\ItemTranslation;

use App\Models\Author;
use App\Models\Context;
use App\Models\Item;
use App\Models\ItemTranslation;
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

    public function test_can_update_item_translation(): void
    {
        $translation = ItemTranslation::factory()->create();
        $data = [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph,
            'type' => $this->faker->word,
        ];

        $response = $this->putJson(route('item-translation.update', ['item_translation' => $translation->id]), $data);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'item_id',
                    'language_id',
                    'context_id',
                    'name',
                    'description',
                    'type',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('item_translations', [
            'id' => $translation->id,
            'name' => $data['name'],
            'description' => $data['description'],
            'type' => $data['type'],
        ]);
    }

    public function test_update_can_modify_all_optional_fields(): void
    {
        $translation = ItemTranslation::factory()->create();
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

        $response = $this->putJson(route('item-translation.update', $translation->id), $updateData);

        $response->assertOk();
        $this->assertDatabaseHas('item_translations', [
            'id' => $translation->id,
            'alternate_name' => $updateData['alternate_name'],
            'type' => $updateData['type'],
            'holder' => $updateData['holder'],
            'owner' => $updateData['owner'],
            'initial_owner' => $updateData['initial_owner'],
            'dates' => $updateData['dates'],
            'location' => $updateData['location'],
            'dimensions' => $updateData['dimensions'],
            'place_of_production' => $updateData['place_of_production'],
            'method_for_datation' => $updateData['method_for_datation'],
            'method_for_provenance' => $updateData['method_for_provenance'],
            'obtention' => $updateData['obtention'],
            'bibliography' => $updateData['bibliography'],
            'author_id' => $updateData['author_id'],
            'text_copy_editor_id' => $updateData['text_copy_editor_id'],
            'translator_id' => $updateData['translator_id'],
            'translation_copy_editor_id' => $updateData['translation_copy_editor_id'],
            'backward_compatibility' => $updateData['backward_compatibility'],
        ]);
    }

    public function test_update_can_change_item_if_combination_remains_unique(): void
    {
        $translation = ItemTranslation::factory()->create();
        $newItem = Item::factory()->withoutTranslations()->create();

        $updateData = [
            'item_id' => $newItem->id,
        ];

        $response = $this->putJson(route('item-translation.update', $translation->id), $updateData);

        $response->assertOk()
            ->assertJsonPath('data.item_id', $newItem->id);

        $this->assertDatabaseHas('item_translations', [
            'id' => $translation->id,
            'item_id' => $newItem->id,
        ]);
    }

    public function test_update_can_change_language_if_combination_remains_unique(): void
    {
        $translation = ItemTranslation::factory()->create();
        $newLanguage = Language::factory()->create();

        $updateData = [
            'language_id' => $newLanguage->id,
        ];

        $response = $this->putJson(route('item-translation.update', $translation->id), $updateData);

        $response->assertOk()
            ->assertJsonPath('data.language_id', $newLanguage->id);

        $this->assertDatabaseHas('item_translations', [
            'id' => $translation->id,
            'language_id' => $newLanguage->id,
        ]);
    }

    public function test_update_can_change_context_if_combination_remains_unique(): void
    {
        $translation = ItemTranslation::factory()->create();
        $newContext = Context::factory()->create();

        $updateData = [
            'context_id' => $newContext->id,
        ];

        $response = $this->putJson(route('item-translation.update', $translation->id), $updateData);

        $response->assertOk()
            ->assertJsonPath('data.context_id', $newContext->id);

        $this->assertDatabaseHas('item_translations', [
            'id' => $translation->id,
            'context_id' => $newContext->id,
        ]);
    }

    public function test_update_validates_unique_constraint_on_item_language_context_combination(): void
    {
        $translation1 = ItemTranslation::factory()->create();
        $translation2 = ItemTranslation::factory()->create();

        // Try to update translation2 to have the same item, language, and context as translation1
        $updateData = [
            'item_id' => $translation1->item_id,
            'language_id' => $translation1->language_id,
            'context_id' => $translation1->context_id,
        ];

        $response = $this->putJson(route('item-translation.update', $translation2->id), $updateData);

        $response->assertUnprocessable();
    }

    public function test_update_allows_null_values_for_optional_fields(): void
    {
        $translation = ItemTranslation::factory()->create([
            'alternate_name' => 'Original Name',
            'type' => 'Original Type',
            'holder' => 'Original Holder',
        ]);

        $updateData = [
            'alternate_name' => null,
            'type' => null,
            'holder' => null,
        ];

        $response = $this->putJson(route('item-translation.update', $translation->id), $updateData);

        $response->assertOk();
        $translation->refresh();
        $this->assertNull($translation->alternate_name);
        $this->assertNull($translation->type);
        $this->assertNull($translation->holder);
    }

    public function test_update_preserves_unchanged_fields(): void
    {
        $translation = ItemTranslation::factory()->create();
        $originalName = $translation->name;
        $originalDescription = $translation->description;

        $updateData = [
            'type' => 'Updated Type',
        ];

        $response = $this->putJson(route('item-translation.update', $translation->id), $updateData);

        $response->assertOk()
            ->assertJsonPath('data.name', $originalName)
            ->assertJsonPath('data.description', $originalDescription)
            ->assertJsonPath('data.type', $updateData['type']);
    }

    public function test_update_validates_item_id_exists(): void
    {
        $translation = ItemTranslation::factory()->create();
        $updateData = [
            'item_id' => 'non-existent-id',
        ];

        $response = $this->putJson(route('item-translation.update', $translation->id), $updateData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_id']);
    }

    public function test_update_validates_language_id_exists(): void
    {
        $translation = ItemTranslation::factory()->create();
        $updateData = [
            'language_id' => 'xxx',
        ];

        $response = $this->putJson(route('item-translation.update', $translation->id), $updateData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['language_id']);
    }

    public function test_update_validates_context_id_exists(): void
    {
        $translation = ItemTranslation::factory()->create();
        $updateData = [
            'context_id' => 'non-existent-id',
        ];

        $response = $this->putJson(route('item-translation.update', $translation->id), $updateData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['context_id']);
    }

    public function test_update_validates_author_id_exists(): void
    {
        $translation = ItemTranslation::factory()->create();
        $updateData = [
            'author_id' => 'non-existent-id',
        ];

        $response = $this->putJson(route('item-translation.update', $translation->id), $updateData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['author_id']);
    }

    public function test_update_returns_not_found_for_non_existent_item_translation(): void
    {
        $data = [
            'name' => $this->faker->words(3, true),
        ];

        $response = $this->putJson(route('item-translation.update', ['item_translation' => 'non-existent-id']), $data);

        $response->assertNotFound();
    }
}

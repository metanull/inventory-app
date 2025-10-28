<?php

namespace Tests\Feature\Api\ItemTranslation;

use App\Models\ItemTranslation;
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

        $this->user = $this->createVisitorUser();
        $this->actingAs($this->user);
    }

    public function test_show_returns_the_default_structure_without_relations(): void
    {
        $translation = ItemTranslation::factory()->create();

        $response = $this->getJson(route('item-translation.show', ['item_translation' => $translation->id]));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'item_id',
                    'language_id',
                    'context_id',
                    'name',
                    'alternate_name',
                    'description',
                    'type',
                    'holder',
                    'owner',
                    'initial_owner',
                    'dates',
                    'location',
                    'dimensions',
                    'place_of_production',
                    'method_for_datation',
                    'method_for_provenance',
                    'obtention',
                    'bibliography',
                    'author_id',
                    'text_copy_editor_id',
                    'translator_id',
                    'translation_copy_editor_id',
                    'backward_compatibility',
                    'extra',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.id', $translation->id)
            ->assertJsonPath('data.item_id', $translation->item_id)
            ->assertJsonPath('data.language_id', $translation->language_id)
            ->assertJsonPath('data.context_id', $translation->context_id)
            ->assertJsonPath('data.name', $translation->name);
    }

    public function test_show_returns_the_expected_structure_with_all_relations_loaded(): void
    {
        $translation = ItemTranslation::factory()->withAllAuthors()->create();

        $response = $this->getJson(route('item-translation.show', [
            'item_translation' => $translation->id,
            'include' => 'item,language,context,author,textCopyEditor,translator,translationCopyEditor',
        ]));

        $response->assertOk();

        $responseData = $response->json('data');

        // Check that relationship fields are present
        $this->assertArrayHasKey('item', $responseData);
        $this->assertArrayHasKey('language', $responseData);
        $this->assertArrayHasKey('context', $responseData);
        $this->assertArrayHasKey('author', $responseData);
        $this->assertArrayHasKey('text_copy_editor', $responseData);
        $this->assertArrayHasKey('translator', $responseData);
        $this->assertArrayHasKey('translation_copy_editor', $responseData);
    }

    public function test_show_returns_not_found_for_non_existent_item_translation(): void
    {
        $response = $this->getJson(route('item-translation.show', ['item_translation' => 'non-existent-id']));

        $response->assertNotFound();
    }
}

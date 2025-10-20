<?php

namespace Tests\Feature\Api\CollectionTranslation;

use App\Models\CollectionTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class ShowTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createVisitorUser();
        $this->actingAs($this->user);
    }

    public function test_show_returns_the_default_structure_without_relations(): void
    {
        $translation = CollectionTranslation::factory()->create();

        $response = $this->getJson(route('collection-translation.show', ['collection_translation' => $translation->id]));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'collection_id',
                    'language_id',
                    'context_id',
                    'title',
                    'description',
                    'url',
                    'backward_compatibility',
                    'extra',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.id', $translation->id)
            ->assertJsonPath('data.collection_id', $translation->collection_id)
            ->assertJsonPath('data.language_id', $translation->language_id)
            ->assertJsonPath('data.context_id', $translation->context_id)
            ->assertJsonPath('data.title', $translation->title);
    }

    public function test_show_returns_the_expected_structure_with_all_relations_loaded(): void
    {
        $translation = CollectionTranslation::factory()->create();

        $response = $this->getJson(route('collection-translation.show', [
            'collection_translation' => $translation->id,
            'include' => 'collection,language,context',
        ]));

        $response->assertOk();

        $responseData = $response->json('data');

        // Check that relationship fields are present
        $this->assertArrayHasKey('collection', $responseData);
        $this->assertArrayHasKey('language', $responseData);
        $this->assertArrayHasKey('context', $responseData);
    }

    public function test_show_returns_not_found_for_non_existent_collection_translation(): void
    {
        $response = $this->getJson(route('collection-translation.show', ['collection_translation' => 'non-existent-id']));

        $response->assertNotFound();
    }
}

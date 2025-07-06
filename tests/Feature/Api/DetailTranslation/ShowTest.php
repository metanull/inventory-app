<?php

namespace Tests\Feature\Api\DetailTranslation;

use App\Models\DetailTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_show_detail_translation(): void
    {
        $translation = DetailTranslation::factory()->create();

        $response = $this->getJson(route('detail-translation.show', ['detail_translation' => $translation->id]));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'detail_id',
                    'language_id',
                    'context_id',
                    'name',
                    'alternate_name',
                    'description',
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
            ->assertJsonPath('data.detail_id', $translation->detail_id)
            ->assertJsonPath('data.language_id', $translation->language_id)
            ->assertJsonPath('data.context_id', $translation->context_id)
            ->assertJsonPath('data.name', $translation->name);
    }

    public function test_show_includes_relationship_data(): void
    {
        $translation = DetailTranslation::factory()->withAllAuthors()->create();

        $response = $this->getJson(route('detail-translation.show', ['detail_translation' => $translation->id]));

        $response->assertOk();

        $responseData = $response->json('data');

        // Check that relationship fields are present
        $this->assertArrayHasKey('detail', $responseData);
        $this->assertArrayHasKey('language', $responseData);
        $this->assertArrayHasKey('context', $responseData);
        $this->assertArrayHasKey('author', $responseData);
        $this->assertArrayHasKey('text_copy_editor', $responseData);
        $this->assertArrayHasKey('translator', $responseData);
        $this->assertArrayHasKey('translation_copy_editor', $responseData);

        // Verify author relationships if they exist
        if ($translation->author_id) {
            $this->assertNotNull($responseData['author']);
            $this->assertEquals($translation->author_id, $responseData['author']['id']);
        }
        if ($translation->text_copy_editor_id) {
            $this->assertNotNull($responseData['text_copy_editor']);
            $this->assertEquals($translation->text_copy_editor_id, $responseData['text_copy_editor']['id']);
        }
        if ($translation->translator_id) {
            $this->assertNotNull($responseData['translator']);
            $this->assertEquals($translation->translator_id, $responseData['translator']['id']);
        }
        if ($translation->translation_copy_editor_id) {
            $this->assertNotNull($responseData['translation_copy_editor']);
            $this->assertEquals($translation->translation_copy_editor_id, $responseData['translation_copy_editor']['id']);
        }
    }

    public function test_show_returns_not_found_for_non_existent_detail_translation(): void
    {
        $response = $this->getJson(route('detail-translation.show', ['detail_translation' => 'non-existent-id']));

        $response->assertNotFound();
    }
}

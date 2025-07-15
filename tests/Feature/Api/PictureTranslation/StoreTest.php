<?php

namespace Tests\Feature\Api\PictureTranslation;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_authenticated_user_can_create_picture_translation(): void
    {
        $pictureTranslationData = \App\Models\PictureTranslation::factory()->make()->toArray();

        // Convert extra field to JSON string for API request
        if (isset($pictureTranslationData['extra']) && is_array($pictureTranslationData['extra'])) {
            $pictureTranslationData['extra'] = json_encode($pictureTranslationData['extra']);
        }

        $response = $this->postJson('/api/picture-translation', $pictureTranslationData);
        $response->assertCreated();
        $response->assertJsonPath('data.description', $pictureTranslationData['description']);
        $response->assertJsonPath('data.caption', $pictureTranslationData['caption']);
        $response->assertJsonPath('data.picture_id', $pictureTranslationData['picture_id']);
        $response->assertJsonPath('data.language_id', $pictureTranslationData['language_id']);
        $response->assertJsonPath('data.context_id', $pictureTranslationData['context_id']);
    }

    public function test_create_picture_translation_stores_in_database(): void
    {
        $pictureTranslationData = \App\Models\PictureTranslation::factory()->make()->toArray();

        // Convert extra field to JSON string if it's an array
        if (isset($pictureTranslationData['extra']) && is_array($pictureTranslationData['extra'])) {
            $pictureTranslationData['extra'] = json_encode($pictureTranslationData['extra']);
        }

        $response = $this->postJson('/api/picture-translation', $pictureTranslationData);
        $response->assertCreated();

        $this->assertDatabaseHas('picture_translations', [
            'description' => $pictureTranslationData['description'],
            'caption' => $pictureTranslationData['caption'],
            'picture_id' => $pictureTranslationData['picture_id'],
            'language_id' => $pictureTranslationData['language_id'],
            'context_id' => $pictureTranslationData['context_id'],
        ]);
    }

    public function test_create_picture_translation_returns_correct_structure(): void
    {
        $pictureTranslationData = \App\Models\PictureTranslation::factory()->make()->toArray();

        // Convert extra field to JSON string for API request
        if (isset($pictureTranslationData['extra']) && is_array($pictureTranslationData['extra'])) {
            $pictureTranslationData['extra'] = json_encode($pictureTranslationData['extra']);
        }

        $response = $this->postJson('/api/picture-translation', $pictureTranslationData);
        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'picture_id',
                'language_id',
                'context_id',
                'description',
                'caption',
                'author_id',
                'text_copy_editor_id',
                'translator_id',
                'translation_copy_editor_id',
                'backward_compatibility',
                'extra',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_create_picture_translation_requires_description(): void
    {
        $pictureTranslationData = \App\Models\PictureTranslation::factory()->make()->except(['description']);

        $response = $this->postJson('/api/picture-translation', $pictureTranslationData);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['description']);
    }

    public function test_create_picture_translation_requires_caption(): void
    {
        $pictureTranslationData = \App\Models\PictureTranslation::factory()->make()->except(['caption']);

        $response = $this->postJson('/api/picture-translation', $pictureTranslationData);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['caption']);
    }

    public function test_create_picture_translation_requires_picture_id(): void
    {
        $pictureTranslationData = \App\Models\PictureTranslation::factory()->make()->except(['picture_id']);

        $response = $this->postJson('/api/picture-translation', $pictureTranslationData);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['picture_id']);
    }

    public function test_create_picture_translation_requires_language_id(): void
    {
        $pictureTranslationData = \App\Models\PictureTranslation::factory()->make()->except(['language_id']);

        $response = $this->postJson('/api/picture-translation', $pictureTranslationData);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    public function test_create_picture_translation_requires_context_id(): void
    {
        $pictureTranslationData = \App\Models\PictureTranslation::factory()->make()->except(['context_id']);

        $response = $this->postJson('/api/picture-translation', $pictureTranslationData);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['context_id']);
    }

    public function test_create_picture_translation_prevents_duplicate_unique_constraint(): void
    {
        $existingTranslation = \App\Models\PictureTranslation::factory()->create();
        $duplicateData = [
            'picture_id' => $existingTranslation->picture_id,
            'language_id' => $existingTranslation->language_id,
            'context_id' => $existingTranslation->context_id,
            'description' => 'Different description',
            'caption' => 'Different caption',
        ];

        $response = $this->postJson('/api/picture-translation', $duplicateData);
        $response->assertUnprocessable();
    }

    public function test_create_picture_translation_allows_nullable_fields(): void
    {
        $pictureTranslationData = \App\Models\PictureTranslation::factory()->make([
            'author_id' => null,
            'text_copy_editor_id' => null,
            'translator_id' => null,
            'translation_copy_editor_id' => null,
            'backward_compatibility' => null,
            'extra' => null,
        ])->toArray();

        $response = $this->postJson('/api/picture-translation', $pictureTranslationData);
        $response->assertCreated();
        $response->assertJsonPath('data.author_id', null);
        $response->assertJsonPath('data.text_copy_editor_id', null);
        $response->assertJsonPath('data.translator_id', null);
        $response->assertJsonPath('data.translation_copy_editor_id', null);
        $response->assertJsonPath('data.backward_compatibility', null);
        $response->assertJsonPath('data.extra', null);
    }
}

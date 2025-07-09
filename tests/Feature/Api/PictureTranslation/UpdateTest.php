<?php

namespace Tests\Feature\Api\PictureTranslation;

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

    public function test_authenticated_user_can_update_picture_translation(): void
    {
        $pictureTranslation = \App\Models\PictureTranslation::factory()->create();
        $updateData = [
            'description' => 'Updated description',
            'caption' => 'Updated caption',
        ];

        $response = $this->putJson("/api/picture-translation/{$pictureTranslation->id}", $updateData);
        $response->assertOk();
        $response->assertJsonPath('data.description', $updateData['description']);
        $response->assertJsonPath('data.caption', $updateData['caption']);
    }

    public function test_update_picture_translation_stores_in_database(): void
    {
        $pictureTranslation = \App\Models\PictureTranslation::factory()->create();
        $updateData = [
            'description' => 'Updated description',
            'caption' => 'Updated caption',
        ];

        $response = $this->putJson("/api/picture-translation/{$pictureTranslation->id}", $updateData);
        $response->assertOk();

        $this->assertDatabaseHas('picture_translations', [
            'id' => $pictureTranslation->id,
            'description' => $updateData['description'],
            'caption' => $updateData['caption'],
        ]);
    }

    public function test_update_picture_translation_returns_correct_structure(): void
    {
        $pictureTranslation = \App\Models\PictureTranslation::factory()->create();
        $updateData = ['description' => 'Updated description'];

        $response = $this->putJson("/api/picture-translation/{$pictureTranslation->id}", $updateData);
        $response->assertOk();
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

    public function test_update_nonexistent_picture_translation_returns_404(): void
    {
        $updateData = ['description' => 'Updated description'];
        $response = $this->putJson('/api/picture-translation/non-existent-id', $updateData);
        $response->assertNotFound();
    }

    public function test_update_picture_translation_validates_description(): void
    {
        $pictureTranslation = \App\Models\PictureTranslation::factory()->create();
        $updateData = ['description' => ''];

        $response = $this->putJson("/api/picture-translation/{$pictureTranslation->id}", $updateData);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['description']);
    }

    public function test_update_picture_translation_validates_caption(): void
    {
        $pictureTranslation = \App\Models\PictureTranslation::factory()->create();
        $updateData = ['caption' => ''];

        $response = $this->putJson("/api/picture-translation/{$pictureTranslation->id}", $updateData);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['caption']);
    }

    public function test_update_picture_translation_allows_partial_updates(): void
    {
        $pictureTranslation = \App\Models\PictureTranslation::factory()->create();
        $originalDescription = $pictureTranslation->description;
        $updateData = ['caption' => 'Updated caption only'];

        $response = $this->putJson("/api/picture-translation/{$pictureTranslation->id}", $updateData);
        $response->assertOk();
        $response->assertJsonPath('data.caption', $updateData['caption']);
        $response->assertJsonPath('data.description', $originalDescription);
    }

    public function test_update_picture_translation_prevents_duplicate_unique_constraint(): void
    {
        $existingTranslation = \App\Models\PictureTranslation::factory()->create();
        $anotherTranslation = \App\Models\PictureTranslation::factory()->create();

        $updateData = [
            'picture_id' => $existingTranslation->picture_id,
            'language_id' => $existingTranslation->language_id,
            'context_id' => $existingTranslation->context_id,
        ];

        $response = $this->putJson("/api/picture-translation/{$anotherTranslation->id}", $updateData);
        $response->assertUnprocessable();
    }

    public function test_update_picture_translation_allows_nullable_fields(): void
    {
        $pictureTranslation = \App\Models\PictureTranslation::factory()->withAuthors()->create();
        $updateData = [
            'author_id' => null,
            'text_copy_editor_id' => null,
            'translator_id' => null,
            'translation_copy_editor_id' => null,
            'backward_compatibility' => null,
            'extra' => null,
        ];

        $response = $this->putJson("/api/picture-translation/{$pictureTranslation->id}", $updateData);
        $response->assertOk();
        $response->assertJsonPath('data.author_id', null);
        $response->assertJsonPath('data.text_copy_editor_id', null);
        $response->assertJsonPath('data.translator_id', null);
        $response->assertJsonPath('data.translation_copy_editor_id', null);
        $response->assertJsonPath('data.backward_compatibility', null);
        $response->assertJsonPath('data.extra', null);
    }
}

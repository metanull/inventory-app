<?php

namespace Tests\Feature\Api\PictureTranslation;

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

    public function test_authenticated_user_can_show_picture_translation(): void
    {
        $pictureTranslation = \App\Models\PictureTranslation::factory()->create();

        $response = $this->getJson("/api/picture-translation/{$pictureTranslation->id}");
        $response->assertOk();
        $response->assertJsonPath('data.id', $pictureTranslation->id);
        $response->assertJsonPath('data.picture_id', $pictureTranslation->picture_id);
        $response->assertJsonPath('data.language_id', $pictureTranslation->language_id);
        $response->assertJsonPath('data.context_id', $pictureTranslation->context_id);
        $response->assertJsonPath('data.description', $pictureTranslation->description);
        $response->assertJsonPath('data.caption', $pictureTranslation->caption);
    }

    public function test_show_picture_translation_returns_correct_structure(): void
    {
        $pictureTranslation = \App\Models\PictureTranslation::factory()->create();

        $response = $this->getJson("/api/picture-translation/{$pictureTranslation->id}");
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

    public function test_show_nonexistent_picture_translation_returns_404(): void
    {
        $response = $this->getJson('/api/picture-translation/non-existent-id');
        $response->assertNotFound();
    }

    public function test_show_picture_translation_with_relationships(): void
    {
        $pictureTranslation = \App\Models\PictureTranslation::factory()->withAuthors()->create();

        $response = $this->getJson("/api/picture-translation/{$pictureTranslation->id}");
        $response->assertOk();
        $response->assertJsonPath('data.author_id', $pictureTranslation->author_id);
        $response->assertJsonPath('data.text_copy_editor_id', $pictureTranslation->text_copy_editor_id);
        $response->assertJsonPath('data.translator_id', $pictureTranslation->translator_id);
        $response->assertJsonPath('data.translation_copy_editor_id', $pictureTranslation->translation_copy_editor_id);
    }
}

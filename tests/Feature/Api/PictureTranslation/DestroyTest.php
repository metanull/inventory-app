<?php

namespace Tests\Feature\Api\PictureTranslation;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_authenticated_user_can_delete_picture_translation(): void
    {
        $pictureTranslation = \App\Models\PictureTranslation::factory()->create();

        $response = $this->deleteJson("/api/picture-translation/{$pictureTranslation->id}");
        $response->assertNoContent();
    }

    public function test_delete_picture_translation_removes_from_database(): void
    {
        $pictureTranslation = \App\Models\PictureTranslation::factory()->create();

        $response = $this->deleteJson("/api/picture-translation/{$pictureTranslation->id}");
        $response->assertNoContent();

        $this->assertDatabaseMissing('picture_translations', [
            'id' => $pictureTranslation->id,
        ]);
    }

    public function test_delete_nonexistent_picture_translation_returns_404(): void
    {
        $response = $this->deleteJson('/api/picture-translation/non-existent-id');
        $response->assertNotFound();
    }

    public function test_delete_picture_translation_maintains_referential_integrity(): void
    {
        $pictureTranslation = \App\Models\PictureTranslation::factory()->create();
        $pictureId = $pictureTranslation->picture_id;
        $languageId = $pictureTranslation->language_id;
        $contextId = $pictureTranslation->context_id;

        $response = $this->deleteJson("/api/picture-translation/{$pictureTranslation->id}");
        $response->assertNoContent();

        // Verify related records still exist
        $this->assertDatabaseHas('pictures', ['id' => $pictureId]);
        $this->assertDatabaseHas('languages', ['id' => $languageId]);
        $this->assertDatabaseHas('contexts', ['id' => $contextId]);
    }
}

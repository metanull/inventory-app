<?php

namespace Tests\Feature\Api\ThemeTranslation;

use App\Models\ThemeTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DestroyTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_can_delete_theme_translation(): void
    {
        $themeTranslation = ThemeTranslation::factory()->create();

        $response = $this->deleteJson("/api/theme-translation/{$themeTranslation->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('theme_translations', [
            'id' => $themeTranslation->id,
        ]);
    }

    public function test_cannot_delete_nonexistent_theme_translation(): void
    {
        $response = $this->deleteJson('/api/theme-translation/nonexistent-id');

        $response->assertNotFound();
    }

    public function test_delete_theme_translation_removes_record_completely(): void
    {
        $themeTranslation = ThemeTranslation::factory()->create();
        $translationId = $themeTranslation->id;

        $this->assertDatabaseHas('theme_translations', [
            'id' => $translationId,
        ]);

        $response = $this->deleteJson("/api/theme-translation/{$translationId}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('theme_translations', [
            'id' => $translationId,
        ]);

        // Verify the record is completely removed, not soft deleted
        $this->assertDatabaseCount('theme_translations', 0);
    }
}

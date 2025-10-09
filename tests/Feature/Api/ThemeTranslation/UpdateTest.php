<?php

namespace Tests\Feature\Api\ThemeTranslation;

use App\Models\Theme;
use App\Models\ThemeTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class UpdateTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;
    use WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_can_update_theme_translation(): void
    {
        $themeTranslation = ThemeTranslation::factory()->create();
        $data = [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'introduction' => 'Updated Introduction',
        ];

        $response = $this->putJson("/api/theme-translation/{$themeTranslation->id}", $data);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'theme_id',
                    'language_id',
                    'context_id',
                    'title',
                    'description',
                    'introduction',
                    'backward_compatibility',
                    'extra',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.id', $themeTranslation->id)
            ->assertJsonPath('data.title', 'Updated Title')
            ->assertJsonPath('data.description', 'Updated Description')
            ->assertJsonPath('data.introduction', 'Updated Introduction');

        $this->assertDatabaseHas('theme_translations', [
            'id' => $themeTranslation->id,
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'introduction' => 'Updated Introduction',
        ]);
    }

    public function test_can_update_theme_translation_with_new_theme_id(): void
    {
        $themeTranslation = ThemeTranslation::factory()->create();
        $newTheme = Theme::factory()->create();
        $data = [
            'theme_id' => $newTheme->id,
            'title' => $themeTranslation->title,
        ];

        $response = $this->putJson("/api/theme-translation/{$themeTranslation->id}", $data);

        $response->assertOk()
            ->assertJsonPath('data.theme_id', $newTheme->id);

        $this->assertDatabaseHas('theme_translations', [
            'id' => $themeTranslation->id,
            'theme_id' => $newTheme->id,
        ]);
    }

    public function test_cannot_update_nonexistent_theme_translation(): void
    {
        $data = ['name' => 'Updated Name'];

        $response = $this->putJson('/api/theme-translation/nonexistent-id', $data);

        $response->assertNotFound();
    }

    public function test_cannot_update_theme_translation_without_required_fields(): void
    {
        $themeTranslation = ThemeTranslation::factory()->create();
        $data = [
            'theme_id' => null,
            'language_id' => null,
            'context_id' => null,
            'title' => null,
            'description' => null,
            'introduction' => null,
        ];

        $response = $this->putJson("/api/theme-translation/{$themeTranslation->id}", $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['theme_id', 'language_id', 'context_id', 'title', 'description', 'introduction']);
    }

    public function test_cannot_update_theme_translation_with_invalid_theme_id(): void
    {
        $themeTranslation = ThemeTranslation::factory()->create();
        $data = [
            'theme_id' => 'invalid-id',
            'title' => 'Updated Title',
        ];

        $response = $this->putJson("/api/theme-translation/{$themeTranslation->id}", $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['theme_id']);
    }

    public function test_cannot_update_theme_translation_with_nonexistent_theme_id(): void
    {
        $themeTranslation = ThemeTranslation::factory()->create();
        $data = [
            'theme_id' => '550e8400-e29b-41d4-a716-446655440000',
            'title' => 'Updated Title',
        ];

        $response = $this->putJson("/api/theme-translation/{$themeTranslation->id}", $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['theme_id']);
    }

    public function test_cannot_update_theme_translation_with_invalid_language_id(): void
    {
        $themeTranslation = ThemeTranslation::factory()->create();
        $data = [
            'language_id' => 'invalid',
            'title' => 'Updated Title',
        ];

        $response = $this->putJson("/api/theme-translation/{$themeTranslation->id}", $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['language_id']);
    }

    public function test_cannot_update_theme_translation_to_create_duplicate(): void
    {
        $existing = ThemeTranslation::factory()->create();
        $themeTranslation = ThemeTranslation::factory()->create();

        $data = [
            'theme_id' => $existing->theme_id,
            'language_id' => $existing->language_id,
            'context_id' => $existing->context_id,
            'title' => 'Updated Title',
        ];

        $response = $this->putJson("/api/theme-translation/{$themeTranslation->id}", $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['theme_id']);
    }

    public function test_can_update_theme_translation_backward_compatibility(): void
    {
        $themeTranslation = ThemeTranslation::factory()->create(['backward_compatibility' => null]);
        $data = [
            'backward_compatibility' => 'legacy-id-456',
            'title' => $themeTranslation->title,
        ];

        $response = $this->putJson("/api/theme-translation/{$themeTranslation->id}", $data);

        $response->assertOk()
            ->assertJsonPath('data.backward_compatibility', 'legacy-id-456');
    }

    public function test_can_update_theme_translation_description_to_null(): void
    {
        $themeTranslation = ThemeTranslation::factory()->create(['description' => 'Original Description']);
        $data = [
            'description' => null,
            'title' => $themeTranslation->title,
        ];

        $response = $this->putJson("/api/theme-translation/{$themeTranslation->id}", $data);

        $response->assertUnprocessable(); // description is required, so this should fail
    }
}

<?php

namespace Tests\Feature\Api\ThemeTranslation;

use App\Enums\Permission;
use App\Models\ThemeTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class ShowTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith([Permission::VIEW_DATA->value]);
        $this->actingAs($this->user);
    }

    public function test_show_returns_the_default_structure_without_relations(): void
    {
        $themeTranslation = ThemeTranslation::factory()->create();

        $response = $this->getJson("/api/theme-translation/{$themeTranslation->id}");

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
            ->assertJsonPath('data.theme_id', $themeTranslation->theme_id)
            ->assertJsonPath('data.language_id', $themeTranslation->language_id)
            ->assertJsonPath('data.context_id', $themeTranslation->context_id)
            ->assertJsonPath('data.title', $themeTranslation->title)
            ->assertJsonPath('data.description', $themeTranslation->description)
            ->assertJsonPath('data.introduction', $themeTranslation->introduction);
    }

    public function test_cannot_show_nonexistent_theme_translation(): void
    {
        $response = $this->getJson('/api/theme-translation/nonexistent-id');

        $response->assertNotFound();
    }

    public function test_show_theme_translation_includes_theme_relationship(): void
    {
        $themeTranslation = ThemeTranslation::factory()->create();

        $response = $this->getJson("/api/theme-translation/{$themeTranslation->id}");

        $response->assertOk()
            ->assertJsonPath('data.theme_id', $themeTranslation->theme_id);
    }
}

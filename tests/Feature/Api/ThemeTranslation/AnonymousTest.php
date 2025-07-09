<?php

namespace Tests\Feature\Api\ThemeTranslation;

use App\Models\Theme;
use App\Models\ThemeTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_anonymous_cannot_list_theme_translations(): void
    {
        $response = $this->getJson('/api/theme-translation');

        $response->assertUnauthorized();
    }

    public function test_anonymous_cannot_show_theme_translation(): void
    {
        $themeTranslation = ThemeTranslation::factory()->create();

        $response = $this->getJson("/api/theme-translation/{$themeTranslation->id}");

        $response->assertUnauthorized();
    }

    public function test_anonymous_cannot_create_theme_translation(): void
    {
        $theme = Theme::factory()->create();
        $data = ThemeTranslation::factory()->make(['theme_id' => $theme->id])->toArray();

        $response = $this->postJson('/api/theme-translation', $data);

        $response->assertUnauthorized();
    }

    public function test_anonymous_cannot_update_theme_translation(): void
    {
        $themeTranslation = ThemeTranslation::factory()->create();
        $data = ThemeTranslation::factory()->make()->toArray();

        $response = $this->putJson("/api/theme-translation/{$themeTranslation->id}", $data);

        $response->assertUnauthorized();
    }

    public function test_anonymous_cannot_delete_theme_translation(): void
    {
        $themeTranslation = ThemeTranslation::factory()->create();

        $response = $this->deleteJson("/api/theme-translation/{$themeTranslation->id}");

        $response->assertUnauthorized();
    }
}

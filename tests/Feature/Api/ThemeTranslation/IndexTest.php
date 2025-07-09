<?php

namespace Tests\Feature\Api\ThemeTranslation;

use App\Models\Context;
use App\Models\Theme;
use App\Models\ThemeTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Seed required data
        $this->artisan('db:seed', ['--class' => 'LanguageSeeder']);
        $this->artisan('db:seed', ['--class' => 'ContextSeeder']);
    }

    public function test_can_list_theme_translations(): void
    {
        $themeTranslations = ThemeTranslation::factory()->count(3)->create();

        $response = $this->getJson('/api/theme-translation');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
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
                ],
            ]);

        $response->assertJsonPath('data.0.id', $themeTranslations->first()->id);
    }

    public function test_can_list_empty_theme_translations(): void
    {
        $response = $this->getJson('/api/theme-translation');

        $response->assertOk()
            ->assertJsonStructure(['data'])
            ->assertJsonPath('data', []);
    }

    public function test_can_filter_theme_translations_by_theme_id(): void
    {
        $theme1 = Theme::factory()->create();
        $theme2 = Theme::factory()->create();

        $translation1 = ThemeTranslation::factory()->create(['theme_id' => $theme1->id]);
        ThemeTranslation::factory()->create(['theme_id' => $theme2->id]);

        $response = $this->getJson("/api/theme-translation?theme_id={$theme1->id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $translation1->id);
    }

    public function test_can_filter_theme_translations_by_language_id(): void
    {
        $translation1 = ThemeTranslation::factory()->create(['language_id' => 'eng']);
        ThemeTranslation::factory()->create(['language_id' => 'fra']);

        $response = $this->getJson('/api/theme-translation?language_id=eng');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $translation1->id);
    }

    public function test_can_filter_theme_translations_by_context_id(): void
    {
        $context1 = Context::factory()->create();
        $context2 = Context::factory()->create();

        $translation1 = ThemeTranslation::factory()->create(['context_id' => $context1->id]);
        ThemeTranslation::factory()->create(['context_id' => $context2->id]);

        $response = $this->getJson("/api/theme-translation?context_id={$translation1->context_id}");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $translation1->id);
    }
}

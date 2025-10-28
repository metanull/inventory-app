<?php

namespace Tests\Feature\Api\ThemeTranslation;

use App\Enums\Permission;
use App\Models\Context;
use App\Models\Language;
use App\Models\Theme;
use App\Models\ThemeTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class StoreTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith(Permission::dataOperations());
        $this->actingAs($this->user);
    }

    public function test_can_create_theme_translation(): void
    {
        $theme = Theme::factory()->create();
        $context = Context::factory()->create();
        $data = ThemeTranslation::factory()->make([
            'theme_id' => $theme->id,
            'context_id' => $context->id,
        ])->toArray();

        $response = $this->postJson('/api/theme-translation', $data);

        $response->assertCreated()
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
            ->assertJsonPath('data.theme_id', $data['theme_id'])
            ->assertJsonPath('data.language_id', $data['language_id'])
            ->assertJsonPath('data.context_id', $data['context_id'])
            ->assertJsonPath('data.title', $data['title'])
            ->assertJsonPath('data.description', $data['description'])
            ->assertJsonPath('data.introduction', $data['introduction']);

        $this->assertDatabaseHas('theme_translations', [
            'theme_id' => $data['theme_id'],
            'language_id' => $data['language_id'],
            'context_id' => $data['context_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'introduction' => $data['introduction'],
        ]);
    }

    public function test_cannot_create_theme_translation_without_required_fields(): void
    {
        $data = [];

        $response = $this->postJson('/api/theme-translation', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['theme_id', 'language_id', 'context_id', 'title', 'description', 'introduction']);
    }

    public function test_cannot_create_theme_translation_with_invalid_theme_id(): void
    {
        $data = ThemeTranslation::factory()->make(['theme_id' => 'invalid-id'])->toArray();

        $response = $this->postJson('/api/theme-translation', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['theme_id']);
    }

    public function test_cannot_create_theme_translation_with_nonexistent_theme_id(): void
    {
        $data = ThemeTranslation::factory()->make(['theme_id' => '550e8400-e29b-41d4-a716-446655440000'])->toArray();

        $response = $this->postJson('/api/theme-translation', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['theme_id']);
    }

    public function test_cannot_create_theme_translation_with_invalid_language_id(): void
    {
        $theme = Theme::factory()->create();
        $data = ThemeTranslation::factory()->make([
            'theme_id' => $theme->id,
            'language_id' => 'invalid',
        ])->toArray();

        $response = $this->postJson('/api/theme-translation', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['language_id']);
    }

    public function test_cannot_create_duplicate_theme_translation(): void
    {
        $existing = ThemeTranslation::factory()->create();
        $data = [
            'theme_id' => $existing->theme_id,
            'language_id' => $existing->language_id,
            'context_id' => $existing->context_id,
            'title' => 'Different Title',
            'description' => 'Different Description',
            'introduction' => 'Different Introduction',
        ];

        $response = $this->postJson('/api/theme-translation', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['theme_id']);
    }

    public function test_can_create_theme_translation_with_same_theme_but_different_language(): void
    {
        $language1 = Language::factory()->create();
        $language2 = Language::factory()->create();

        $existing = ThemeTranslation::factory()->create(['language_id' => $language1->id]);
        $data = ThemeTranslation::factory()->make([
            'theme_id' => $existing->theme_id,
            'language_id' => $language2->id,
            'context_id' => $existing->context_id,
        ])->toArray();

        $response = $this->postJson('/api/theme-translation', $data);

        $response->assertCreated();
    }

    public function test_can_create_theme_translation_with_same_theme_but_different_context(): void
    {
        $existing = ThemeTranslation::factory()->create();
        $context = Context::factory()->create();
        $data = ThemeTranslation::factory()->make([
            'theme_id' => $existing->theme_id,
            'language_id' => $existing->language_id,
            'context_id' => $context->id,
        ])->toArray();

        $response = $this->postJson('/api/theme-translation', $data);

        $response->assertCreated();
    }

    public function test_can_create_theme_translation_with_optional_backward_compatibility(): void
    {
        $theme = Theme::factory()->create();
        $data = ThemeTranslation::factory()->make([
            'theme_id' => $theme->id,
            'backward_compatibility' => 'legacy-id-123',
        ])->toArray();

        $response = $this->postJson('/api/theme-translation', $data);

        $response->assertCreated()
            ->assertJsonPath('data.backward_compatibility', 'legacy-id-123');
    }

    public function test_validates_required_description(): void
    {
        $theme = Theme::factory()->create();
        $data = ThemeTranslation::factory()->make([
            'theme_id' => $theme->id,
            'description' => null,
        ])->toArray();

        $response = $this->postJson('/api/theme-translation', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['description']);
    }
}

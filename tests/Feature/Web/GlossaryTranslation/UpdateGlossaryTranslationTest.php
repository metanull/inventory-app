<?php

declare(strict_types=1);

namespace Tests\Feature\Web\GlossaryTranslation;

use App\Models\Glossary;
use App\Models\GlossaryTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class UpdateGlossaryTranslationTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    public function test_update_modifies_translation_with_valid_data(): void
    {
        $this->actingAsDataUser();

        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();
        $translation = GlossaryTranslation::factory()
            ->for($glossary)
            ->for($language)
            ->create();

        $data = [
            'language_id' => $language->id,
            'definition' => 'Updated definition text',
        ];

        $response = $this->put(route('glossaries.translations.update', [$glossary, $translation]), $data);

        $this->assertDatabaseHas('glossary_translations', [
            'id' => $translation->id,
            'definition' => 'Updated definition text',
        ]);

        $response->assertRedirect(route('glossaries.translations.show', [$glossary, $translation]));
        $response->assertSessionHas('success');
    }

    public function test_update_validates_required_definition(): void
    {
        $this->actingAsDataUser();

        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();
        $translation = GlossaryTranslation::factory()
            ->for($glossary)
            ->for($language)
            ->create();

        $data = [
            'language_id' => $language->id,
            'definition' => '',
        ];

        $response = $this->put(route('glossaries.translations.update', [$glossary, $translation]), $data);

        $response->assertSessionHasErrors('definition');
    }

    public function test_update_requires_authentication(): void
    {
        $glossary = Glossary::factory()->create();
        $translation = GlossaryTranslation::factory()->for($glossary)->create();

        $data = [
            'language_id' => $translation->language_id,
            'definition' => 'Updated definition',
        ];

        $response = $this->put(route('glossaries.translations.update', [$glossary, $translation]), $data);

        $response->assertRedirect(route('login'));
    }

    public function test_update_requires_update_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();
        $translation = GlossaryTranslation::factory()->for($glossary)->create();

        $data = [
            'language_id' => $translation->language_id,
            'definition' => 'Updated definition',
        ];

        $response = $this->put(route('glossaries.translations.update', [$glossary, $translation]), $data);

        $response->assertForbidden();
    }
}

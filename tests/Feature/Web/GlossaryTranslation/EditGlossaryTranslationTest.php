<?php

declare(strict_types=1);

namespace Tests\Feature\Web\GlossaryTranslation;

use App\Models\Glossary;
use App\Models\GlossaryTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class EditGlossaryTranslationTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    public function test_edit_displays_form_with_translation_data(): void
    {
        $this->actingAsDataUser();

        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();
        $translation = GlossaryTranslation::factory()
            ->for($glossary)
            ->for($language)
            ->create();

        $response = $this->get(route('glossaries.translations.edit', [$glossary, $translation]));

        $response->assertOk();
        $response->assertViewIs('glossary-translation.edit');
        $response->assertViewHas('translation');
        $response->assertSee($translation->definition);
    }

    public function test_edit_requires_authentication(): void
    {
        $glossary = Glossary::factory()->create();
        $translation = GlossaryTranslation::factory()->for($glossary)->create();

        $response = $this->get(route('glossaries.translations.edit', [$glossary, $translation]));

        $response->assertRedirect(route('login'));
    }

    public function test_edit_requires_update_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();
        $translation = GlossaryTranslation::factory()->for($glossary)->create();

        $response = $this->get(route('glossaries.translations.edit', [$glossary, $translation]));

        $response->assertForbidden();
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Web\GlossaryTranslation;

use App\Models\Glossary;
use App\Models\GlossaryTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class ShowGlossaryTranslationTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    public function test_show_displays_translation(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();
        $translation = GlossaryTranslation::factory()
            ->for($glossary)
            ->for($language)
            ->create();

        $response = $this->get(route('glossaries.translations.show', [$glossary, $translation]));

        $response->assertOk();
        $response->assertViewIs('glossary-translation.show');
        $response->assertViewHas('translation');
        $response->assertSee($translation->definition);
        $response->assertSee($language->internal_name);
    }

    public function test_show_requires_authentication(): void
    {
        $glossary = Glossary::factory()->create();
        $translation = GlossaryTranslation::factory()->for($glossary)->create();

        $response = $this->get(route('glossaries.translations.show', [$glossary, $translation]));

        $response->assertRedirect(route('login'));
    }

    public function test_show_requires_view_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();
        $translation = GlossaryTranslation::factory()->for($glossary)->create();

        $response = $this->get(route('glossaries.translations.show', [$glossary, $translation]));

        $response->assertForbidden();
    }
}

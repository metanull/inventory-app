<?php

declare(strict_types=1);

namespace Tests\Feature\Web\GlossaryTranslation;

use App\Models\Glossary;
use App\Models\GlossaryTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class IndexGlossaryTranslationTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    public function test_index_displays_translations(): void
    {
        $this->actingAsDataUser();

        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();
        $translation = GlossaryTranslation::factory()
            ->for($glossary)
            ->for($language)
            ->create();

        $response = $this->get(route('glossaries.translations.index', $glossary));

        $response->assertOk();
        $response->assertViewIs('glossary-translation.index');
        $response->assertViewHas('translations');
        $response->assertSee($language->internal_name);
        // Definition is truncated in the view, so we check for the beginning only
        $response->assertSee(substr($translation->definition, 0, 50));
    }

    public function test_index_requires_authentication(): void
    {
        $glossary = Glossary::factory()->create();

        $response = $this->get(route('glossaries.translations.index', $glossary));

        $response->assertRedirect(route('login'));
    }

    public function test_index_requires_view_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();

        $response = $this->get(route('glossaries.translations.index', $glossary));

        $response->assertForbidden();
    }
}

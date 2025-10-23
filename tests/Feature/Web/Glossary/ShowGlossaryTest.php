<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Glossary;

use App\Models\Glossary;
use App\Models\GlossarySpelling;
use App\Models\GlossaryTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class ShowGlossaryTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    public function test_show_displays_glossary(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();

        $response = $this->get(route('glossaries.show', $glossary));

        $response->assertOk();
        $response->assertViewIs('glossary.show');
        $response->assertViewHas('glossary');
        $response->assertSee($glossary->internal_name);
    }

    public function test_show_displays_translations(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();
        $translation = GlossaryTranslation::factory()
            ->for($glossary)
            ->for($language)
            ->create();

        $response = $this->get(route('glossaries.show', $glossary));

        $response->assertOk();
        $response->assertSee($translation->definition);
    }

    public function test_show_displays_spellings(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()
            ->for($glossary)
            ->for($language)
            ->create();

        $response = $this->get(route('glossaries.show', $glossary));

        $response->assertOk();
        $response->assertSee($spelling->spelling);
    }

    public function test_show_displays_synonyms(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $glossary1 = Glossary::factory()->create(['internal_name' => 'primary-term']);
        $glossary2 = Glossary::factory()->create(['internal_name' => 'synonym-term']);

        $glossary1->synonyms()->attach($glossary2->id);

        $response = $this->get(route('glossaries.show', $glossary1));

        $response->assertOk();
        $response->assertSee('synonym-term');
    }

    public function test_show_requires_authentication(): void
    {
        $glossary = Glossary::factory()->create();

        $response = $this->get(route('glossaries.show', $glossary));

        $response->assertRedirect(route('login'));
    }

    public function test_show_requires_view_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();

        $response = $this->get(route('glossaries.show', $glossary));

        $response->assertForbidden();
    }
}

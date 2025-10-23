<?php

declare(strict_types=1);

namespace Tests\Feature\Web\GlossarySpelling;

use App\Models\Glossary;
use App\Models\GlossarySpelling;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class ShowGlossarySpellingTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    public function test_show_displays_spelling(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()
            ->for($glossary)
            ->for($language)
            ->create();

        $response = $this->get(route('glossaries.spellings.show', [$glossary, $spelling]));

        $response->assertOk();
        $response->assertViewIs('glossary-spelling.show');
        $response->assertViewHas('spelling');
        $response->assertSee($spelling->spelling);
        $response->assertSee($language->internal_name);
    }

    public function test_show_requires_authentication(): void
    {
        $glossary = Glossary::factory()->create();
        $spelling = GlossarySpelling::factory()->for($glossary)->create();

        $response = $this->get(route('glossaries.spellings.show', [$glossary, $spelling]));

        $response->assertRedirect(route('login'));
    }

    public function test_show_requires_view_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();
        $spelling = GlossarySpelling::factory()->for($glossary)->create();

        $response = $this->get(route('glossaries.spellings.show', [$glossary, $spelling]));

        $response->assertForbidden();
    }
}

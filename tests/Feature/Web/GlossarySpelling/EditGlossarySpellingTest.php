<?php

declare(strict_types=1);

namespace Tests\Feature\Web\GlossarySpelling;

use App\Models\Glossary;
use App\Models\GlossarySpelling;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class EditGlossarySpellingTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    public function test_edit_displays_form_with_spelling_data(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()
            ->for($glossary)
            ->for($language)
            ->create();

        $response = $this->get(route('glossaries.spellings.edit', [$glossary, $spelling]));

        $response->assertOk();
        $response->assertViewIs('glossary-spelling.edit');
        $response->assertViewHas('spelling');
        $response->assertSee($spelling->spelling);
    }

    public function test_edit_requires_authentication(): void
    {
        $glossary = Glossary::factory()->create();
        $spelling = GlossarySpelling::factory()->for($glossary)->create();

        $response = $this->get(route('glossaries.spellings.edit', [$glossary, $spelling]));

        $response->assertRedirect(route('login'));
    }

    public function test_edit_requires_update_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();
        $spelling = GlossarySpelling::factory()->for($glossary)->create();

        $response = $this->get(route('glossaries.spellings.edit', [$glossary, $spelling]));

        $response->assertForbidden();
    }
}

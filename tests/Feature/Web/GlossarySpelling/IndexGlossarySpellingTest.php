<?php

declare(strict_types=1);

namespace Tests\Feature\Web\GlossarySpelling;

use App\Models\Glossary;
use App\Models\GlossarySpelling;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class IndexGlossarySpellingTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    public function test_index_displays_spellings(): void
    {
        $this->actingAsDataUser();

        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()
            ->for($glossary)
            ->for($language)
            ->create();

        $response = $this->get(route('glossaries.spellings.index', $glossary));

        $response->assertOk();
        $response->assertViewIs('glossary-spelling.index');
        $response->assertViewHas('spellings');
        $response->assertSee($spelling->spelling);
    }

    public function test_index_requires_authentication(): void
    {
        $glossary = Glossary::factory()->create();

        $response = $this->get(route('glossaries.spellings.index', $glossary));

        $response->assertRedirect(route('login'));
    }

    public function test_index_requires_view_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();

        $response = $this->get(route('glossaries.spellings.index', $glossary));

        $response->assertForbidden();
    }
}

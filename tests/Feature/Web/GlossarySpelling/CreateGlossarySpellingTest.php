<?php

declare(strict_types=1);

namespace Tests\Feature\Web\GlossarySpelling;

use App\Models\Glossary;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class CreateGlossarySpellingTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    public function test_create_displays_form(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();
        Language::factory()->create();

        $response = $this->get(route('glossaries.spellings.create', $glossary));

        $response->assertOk();
        $response->assertViewIs('glossary-spelling.create');
        $response->assertSee('Language');
        $response->assertSee('Spelling');
    }

    public function test_create_requires_authentication(): void
    {
        $glossary = Glossary::factory()->create();

        $response = $this->get(route('glossaries.spellings.create', $glossary));

        $response->assertRedirect(route('login'));
    }

    public function test_create_requires_create_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();

        $response = $this->get(route('glossaries.spellings.create', $glossary));

        $response->assertForbidden();
    }
}

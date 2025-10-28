<?php

declare(strict_types=1);

namespace Tests\Feature\Web\GlossarySpelling;

use App\Models\Glossary;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class CreateGlossarySpellingTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    public function test_create_displays_form(): void
    {
        $this->actingAsDataUser();

        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create(['internal_name' => 'Test Language']);

        $response = $this->get(route('glossaries.spellings.create', $glossary));

        $response->assertOk();
        $response->assertViewIs('glossary-spelling.create');
        $response->assertSee('Language');
        $response->assertSee('Spelling');
        // Verify form has the correct action and method
        $response->assertSee('action="'.route('glossaries.spellings.store', $glossary).'"', false);
        $response->assertSee('name="language_id"', false);
        $response->assertSee('name="spelling"', false);
        // Verify that languages are available in the select dropdown
        $response->assertSee('Test Language', false);
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

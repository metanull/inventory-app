<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Glossary;

use App\Models\Glossary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class EditGlossaryTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    public function test_edit_displays_form_with_glossary_data(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();

        $response = $this->get(route('glossaries.edit', $glossary));

        $response->assertOk();
        $response->assertViewIs('glossary.edit');
        $response->assertViewHas('glossary');
        $response->assertSee($glossary->internal_name);
    }

    public function test_edit_requires_authentication(): void
    {
        $glossary = Glossary::factory()->create();

        $response = $this->get(route('glossaries.edit', $glossary));

        $response->assertRedirect(route('login'));
    }

    public function test_edit_requires_update_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();

        $response = $this->get(route('glossaries.edit', $glossary));

        $response->assertForbidden();
    }
}

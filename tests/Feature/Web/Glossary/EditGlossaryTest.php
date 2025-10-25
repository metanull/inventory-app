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

        $glossary = Glossary::factory()->create([
            'internal_name' => 'test-glossary-term',
            'backward_compatibility' => 'legacy-123',
        ]);

        $response = $this->get(route('glossaries.edit', $glossary));

        $response->assertOk();
        $response->assertViewIs('glossary.edit');
        $response->assertViewHas('glossary');
        $response->assertSee($glossary->internal_name);
        // Verify form has correct fields and values
        $response->assertSee('name="internal_name"', false);
        $response->assertSee('value="test-glossary-term"', false);
        $response->assertSee('name="backward_compatibility"', false);
        $response->assertSee('value="legacy-123"', false);
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

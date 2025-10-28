<?php

declare(strict_types=1);

namespace Tests\Feature\Web\GlossarySpelling;

use App\Models\Glossary;
use App\Models\GlossarySpelling;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DestroyGlossarySpellingTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    public function test_destroy_deletes_spelling(): void
    {
        $this->actingAsDataUser();

        $glossary = Glossary::factory()->create();
        $spelling = GlossarySpelling::factory()->for($glossary)->create();

        $response = $this->delete(route('glossaries.spellings.destroy', [$glossary, $spelling]));

        $this->assertModelMissing($spelling);
        $response->assertRedirect(route('glossaries.spellings.index', $glossary));
        $response->assertSessionHas('success');
    }

    public function test_destroy_requires_authentication(): void
    {
        $glossary = Glossary::factory()->create();
        $spelling = GlossarySpelling::factory()->for($glossary)->create();

        $response = $this->delete(route('glossaries.spellings.destroy', [$glossary, $spelling]));

        $response->assertRedirect(route('login'));
        $this->assertModelExists($spelling);
    }

    public function test_destroy_requires_delete_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();
        $spelling = GlossarySpelling::factory()->for($glossary)->create();

        $response = $this->delete(route('glossaries.spellings.destroy', [$glossary, $spelling]));

        $response->assertForbidden();
        $this->assertModelExists($spelling);
    }
}

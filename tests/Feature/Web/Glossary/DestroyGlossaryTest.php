<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Glossary;

use App\Models\Glossary;
use App\Models\GlossarySpelling;
use App\Models\GlossaryTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DestroyGlossaryTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    public function test_destroy_deletes_glossary_and_redirects(): void
    {
        $this->actingAsDataUser();
        $glossary = Glossary::factory()->create();

        $response = $this->delete(route('glossaries.destroy', $glossary));

        $response->assertRedirect(route('glossaries.index'));
        $response->assertSessionHas('success');
        $this->assertModelMissing($glossary);
    }

    public function test_destroy_returns_404_if_glossary_not_found(): void
    {
        $this->actingAsDataUser();

        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();
        $translation = GlossaryTranslation::factory()
            ->for($glossary)
            ->for($language)
            ->create();

        $response = $this->delete(route('glossaries.destroy', $glossary));

        $response->assertRedirect(route('glossaries.index'));
        $this->assertModelMissing($glossary);
        $this->assertModelMissing($translation);
    }

    public function test_destroy_deletes_glossary_with_spellings(): void
    {
        $this->actingAsDataUser();

        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()
            ->for($glossary)
            ->for($language)
            ->create();

        $response = $this->delete(route('glossaries.destroy', $glossary));

        $response->assertRedirect(route('glossaries.index'));
        $this->assertModelMissing($glossary);
        $this->assertModelMissing($spelling);
    }

    public function test_destroy_removes_synonym_relationships(): void
    {
        $this->actingAsDataUser();

        $glossary1 = Glossary::factory()->create();
        $glossary2 = Glossary::factory()->create();

        $glossary1->synonyms()->attach($glossary2->id);

        $response = $this->delete(route('glossaries.destroy', $glossary1));

        $response->assertRedirect(route('glossaries.index'));
        $this->assertModelMissing($glossary1);

        // Glossary2 should still exist but the relationship should be gone
        $this->assertModelExists($glossary2);
        $this->assertEquals(0, $glossary2->synonyms()->count());
    }

    public function test_destroy_requires_authentication(): void
    {
        $glossary = Glossary::factory()->create();

        $response = $this->delete(route('glossaries.destroy', $glossary));

        $response->assertRedirect(route('login'));
        $this->assertModelExists($glossary);
    }

    public function test_destroy_requires_delete_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();

        $response = $this->delete(route('glossaries.destroy', $glossary));

        $response->assertForbidden();
        $this->assertModelExists($glossary);
    }
}

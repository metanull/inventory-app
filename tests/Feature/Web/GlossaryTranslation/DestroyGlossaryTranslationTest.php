<?php

declare(strict_types=1);

namespace Tests\Feature\Web\GlossaryTranslation;

use App\Models\Glossary;
use App\Models\GlossaryTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DestroyGlossaryTranslationTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    public function test_destroy_deletes_translation(): void
    {
        $this->actingAsDataUser();

        $glossary = Glossary::factory()->create();
        $translation = GlossaryTranslation::factory()->for($glossary)->create();

        $response = $this->delete(route('glossaries.translations.destroy', [$glossary, $translation]));

        $this->assertModelMissing($translation);
        $response->assertRedirect(route('glossaries.translations.index', $glossary));
        $response->assertSessionHas('success');
    }

    public function test_destroy_requires_authentication(): void
    {
        $glossary = Glossary::factory()->create();
        $translation = GlossaryTranslation::factory()->for($glossary)->create();

        $response = $this->delete(route('glossaries.translations.destroy', [$glossary, $translation]));

        $response->assertRedirect(route('login'));
        $this->assertModelExists($translation);
    }

    public function test_destroy_requires_delete_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();
        $translation = GlossaryTranslation::factory()->for($glossary)->create();

        $response = $this->delete(route('glossaries.translations.destroy', [$glossary, $translation]));

        $response->assertForbidden();
        $this->assertModelExists($translation);
    }
}

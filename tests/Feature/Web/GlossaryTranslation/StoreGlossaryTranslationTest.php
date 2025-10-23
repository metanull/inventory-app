<?php

declare(strict_types=1);

namespace Tests\Feature\Web\GlossaryTranslation;

use App\Models\Glossary;
use App\Models\GlossaryTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class StoreGlossaryTranslationTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    public function test_store_creates_translation_with_valid_data(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();

        $data = [
            'language_id' => $language->id,
            'definition' => 'Test definition for this glossary entry',
        ];

        $response = $this->post(route('glossaries.translations.store', $glossary), $data);

        $this->assertDatabaseHas('glossary_translations', [
            'glossary_id' => $glossary->id,
            'language_id' => $language->id,
            'definition' => 'Test definition for this glossary entry',
        ]);

        $translation = GlossaryTranslation::where('glossary_id', $glossary->id)->first();
        $response->assertRedirect(route('glossaries.translations.show', [$glossary, $translation]));
        $response->assertSessionHas('success');
    }

    public function test_store_validates_required_language_id(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();

        $data = [
            'definition' => 'Test definition',
        ];

        $response = $this->post(route('glossaries.translations.store', $glossary), $data);

        $response->assertSessionHasErrors('language_id');
        $this->assertDatabaseCount('glossary_translations', 0);
    }

    public function test_store_validates_required_definition(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();

        $data = [
            'language_id' => $language->id,
        ];

        $response = $this->post(route('glossaries.translations.store', $glossary), $data);

        $response->assertSessionHasErrors('definition');
        $this->assertDatabaseCount('glossary_translations', 0);
    }

    public function test_store_requires_authentication(): void
    {
        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();

        $data = [
            'language_id' => $language->id,
            'definition' => 'Test definition',
        ];

        $response = $this->post(route('glossaries.translations.store', $glossary), $data);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('glossary_translations', 0);
    }

    public function test_store_requires_create_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();

        $data = [
            'language_id' => $language->id,
            'definition' => 'Test definition',
        ];

        $response = $this->post(route('glossaries.translations.store', $glossary), $data);

        $response->assertForbidden();
        $this->assertDatabaseCount('glossary_translations', 0);
    }
}

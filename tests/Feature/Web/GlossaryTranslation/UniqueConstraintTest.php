<?php

namespace Tests\Feature\Web\GlossaryTranslation;

use App\Models\Glossary;
use App\Models\GlossaryTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class UniqueConstraintTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    public function test_unique_constraint_prevents_duplicate_glossary_language_context(): void
    {
        $this->actingAsDataUser();

        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();

        // Create first translation
        GlossaryTranslation::factory()->for($glossary)->for($language)->create();

        // Attempt to create second translation for same language
        $response = $this->post(route('glossaries.translations.store', $glossary), [
            'language_id' => $language->id,
            'definition' => 'Another definition',
        ]);

        $response->assertSessionHasErrors('language_id');
        $this->assertEquals(1, $glossary->translations()->count());
    }

    public function test_can_create_translations_for_different_languages(): void
    {
        $this->actingAsDataUser();

        $glossary = Glossary::factory()->create();
        $language1 = Language::factory()->create();
        $language2 = Language::factory()->create();

        // Create first translation
        $response1 = $this->post(route('glossaries.translations.store', $glossary), [
            'language_id' => $language1->id,
            'definition' => 'First definition',
        ]);

        $response1->assertSessionHasNoErrors();

        // Create second translation for different language
        $response2 = $this->post(route('glossaries.translations.store', $glossary), [
            'language_id' => $language2->id,
            'definition' => 'Second definition',
        ]);

        $response2->assertSessionHasNoErrors();
        $this->assertEquals(2, $glossary->translations()->count());
    }

    public function test_can_update_translation_keeping_same_language(): void
    {
        $this->actingAsDataUser();

        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();
        $translation = GlossaryTranslation::factory()->for($glossary)->for($language)->create();

        $response = $this->put(route('glossaries.translations.update', [$glossary, $translation]), [
            'language_id' => $language->id,
            'definition' => 'Updated definition',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals('Updated definition', $translation->fresh()->definition);
    }

    public function test_cannot_update_translation_to_use_existing_language(): void
    {
        $this->actingAsDataUser();

        $glossary = Glossary::factory()->create();
        $language1 = Language::factory()->create();
        $language2 = Language::factory()->create();

        $translation1 = GlossaryTranslation::factory()->for($glossary)->for($language1)->create();
        $translation2 = GlossaryTranslation::factory()->for($glossary)->for($language2)->create();

        // Attempt to update translation2 to use language1 (which is already used by translation1)
        $response = $this->put(route('glossaries.translations.update', [$glossary, $translation2]), [
            'language_id' => $language1->id,
            'definition' => 'Updated definition',
        ]);

        $response->assertSessionHasErrors('language_id');
        $this->assertEquals($language2->id, $translation2->fresh()->language_id);
    }

    public function test_used_languages_are_disabled_in_create_form(): void
    {
        $this->actingAsDataUser();

        $glossary = Glossary::factory()->create();
        $usedLanguage = Language::factory()->create(['internal_name' => 'English']);
        $availableLanguage = Language::factory()->create(['internal_name' => 'French']);

        // Create a translation for the used language
        GlossaryTranslation::factory()->for($glossary)->for($usedLanguage)->create();

        $response = $this->get(route('glossaries.translations.create', $glossary));

        $response->assertOk();
        $response->assertSee('disabled', false); // Check that 'disabled' attribute exists
        $response->assertSee($usedLanguage->internal_name);
        $response->assertSee('already used');
        $response->assertSee($availableLanguage->internal_name);
    }

    public function test_used_languages_are_disabled_in_edit_form_except_current(): void
    {
        $this->actingAsDataUser();

        $glossary = Glossary::factory()->create();
        $language1 = Language::factory()->create(['internal_name' => 'English']);
        $language2 = Language::factory()->create(['internal_name' => 'French']);
        $language3 = Language::factory()->create(['internal_name' => 'Spanish']);

        $translation1 = GlossaryTranslation::factory()->for($glossary)->for($language1)->create();
        $translation2 = GlossaryTranslation::factory()->for($glossary)->for($language2)->create();

        // Edit translation2 - language1 should be disabled, language2 should be enabled (current), language3 should be enabled
        $response = $this->get(route('glossaries.translations.edit', [$glossary, $translation2]));

        $response->assertOk();
        $response->assertSee($language1->internal_name);
        $response->assertSee('already used');
        $response->assertSee($language2->internal_name); // Current language
        $response->assertSee($language3->internal_name); // Available language
    }
}

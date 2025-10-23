<?php

declare(strict_types=1);

namespace Tests\Feature\Web\GlossarySpelling;

use App\Models\Glossary;
use App\Models\GlossarySpelling;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class UpdateGlossarySpellingTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    public function test_update_modifies_spelling_with_valid_data(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()
            ->for($glossary)
            ->for($language)
            ->create();

        $data = [
            'language_id' => $language->id,
            'spelling' => 'updated-spelling-variant',
        ];

        $response = $this->put(route('glossaries.spellings.update', [$glossary, $spelling]), $data);

        $this->assertDatabaseHas('glossary_spellings', [
            'id' => $spelling->id,
            'spelling' => 'updated-spelling-variant',
        ]);

        $response->assertRedirect(route('glossaries.spellings.show', [$glossary, $spelling]));
        $response->assertSessionHas('success');
    }

    public function test_update_validates_required_spelling(): void
    {
        $user = $this->createAuthenticatedUserWithDataPermissions();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()
            ->for($glossary)
            ->for($language)
            ->create();

        $data = [
            'language_id' => $language->id,
            'spelling' => '',
        ];

        $response = $this->put(route('glossaries.spellings.update', [$glossary, $spelling]), $data);

        $response->assertSessionHasErrors('spelling');
    }

    public function test_update_requires_authentication(): void
    {
        $glossary = Glossary::factory()->create();
        $spelling = GlossarySpelling::factory()->for($glossary)->create();

        $data = [
            'language_id' => $spelling->language_id,
            'spelling' => 'updated-spelling',
        ];

        $response = $this->put(route('glossaries.spellings.update', [$glossary, $spelling]), $data);

        $response->assertRedirect(route('login'));
    }

    public function test_update_requires_update_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();
        $spelling = GlossarySpelling::factory()->for($glossary)->create();

        $data = [
            'language_id' => $spelling->language_id,
            'spelling' => 'updated-spelling',
        ];

        $response = $this->put(route('glossaries.spellings.update', [$glossary, $spelling]), $data);

        $response->assertForbidden();
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Web\GlossarySpelling;

use App\Models\Glossary;
use App\Models\GlossarySpelling;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class StoreGlossarySpellingTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    public function test_store_creates_spelling_with_valid_data(): void
    {
        $this->actingAsDataUser();

        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();

        $data = [
            'language_id' => $language->id,
            'spelling' => 'test-spelling-variant',
        ];

        $response = $this->post(route('glossaries.spellings.store', $glossary), $data);

        $this->assertDatabaseHas('glossary_spellings', [
            'glossary_id' => $glossary->id,
            'language_id' => $language->id,
            'spelling' => 'test-spelling-variant',
        ]);

        $spelling = GlossarySpelling::where('glossary_id', $glossary->id)->first();
        $response->assertRedirect(route('glossaries.spellings.show', [$glossary, $spelling]));
        $response->assertSessionHas('success');
    }

    public function test_store_validates_required_language_id(): void
    {
        $this->actingAsDataUser();

        $glossary = Glossary::factory()->create();

        $data = [
            'spelling' => 'test-spelling',
        ];

        $response = $this->post(route('glossaries.spellings.store', $glossary), $data);

        $response->assertSessionHasErrors('language_id');
        $this->assertDatabaseCount('glossary_spellings', 0);
    }

    public function test_store_validates_required_spelling(): void
    {
        $this->actingAsDataUser();

        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();

        $data = [
            'language_id' => $language->id,
        ];

        $response = $this->post(route('glossaries.spellings.store', $glossary), $data);

        $response->assertSessionHasErrors('spelling');
        $this->assertDatabaseCount('glossary_spellings', 0);
    }

    public function test_store_requires_authentication(): void
    {
        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();

        $data = [
            'language_id' => $language->id,
            'spelling' => 'test-spelling',
        ];

        $response = $this->post(route('glossaries.spellings.store', $glossary), $data);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('glossary_spellings', 0);
    }

    public function test_store_requires_create_data_permission(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();

        $data = [
            'language_id' => $language->id,
            'spelling' => 'test-spelling',
        ];

        $response = $this->post(route('glossaries.spellings.store', $glossary), $data);

        $response->assertForbidden();
        $this->assertDatabaseCount('glossary_spellings', 0);
    }
}

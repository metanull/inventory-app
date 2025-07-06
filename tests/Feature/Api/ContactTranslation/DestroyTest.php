<?php

namespace Tests\Feature\Api\ContactTranslation;

use App\Models\ContactTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_delete_contact_translation()
    {
        $contact = \App\Models\Contact::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $translation = ContactTranslation::factory()->create([
            'contact_id' => $contact->id,
            'language_id' => $language->id,
        ]);

        $response = $this->deleteJson(route('contact-translation.destroy', $translation->id));

        $response->assertNoContent();
        $this->assertDatabaseMissing('contact_translations', ['id' => $translation->id]);
    }

    public function test_returns_404_for_nonexistent_contact_translation()
    {
        $response = $this->deleteJson(route('contact-translation.destroy', 'nonexistent-id'));
        $response->assertNotFound();
    }
}

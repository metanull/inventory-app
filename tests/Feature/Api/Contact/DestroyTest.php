<?php

namespace Tests\Feature\Api\Contact;

use App\Models\Contact;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        Language::factory()->count(3)->create();
    }

    #[Test]
    public function it_can_delete_a_contact()
    {
        $contact = Contact::factory()->create();

        // Get the contact languages before deletion
        $contactLanguages = $contact->languages()->get();
        $this->assertGreaterThan(0, $contactLanguages->count());

        $response = $this->deleteJson(route('contact.destroy', ['contact' => $contact->id]));

        $response->assertNoContent();

        // Check that the contact was deleted
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);

        // Check that the related contact_language entries were also deleted (due to cascade)
        foreach ($contactLanguages as $language) {
            $this->assertDatabaseMissing('contact_language', [
                'contact_id' => $contact->id,
                'language_id' => $language->id,
            ]);
        }
    }

    #[Test]
    public function it_returns_not_found_for_non_existent_contact()
    {
        $response = $this->deleteJson(route('contact.destroy', ['contact' => 'non-existent-id']));

        $response->assertNotFound();
    }
}

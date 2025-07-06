<?php

namespace Tests\Unit\Contact;

use App\Models\Contact;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed languages for testing
        Language::factory()->count(3)->create();
    }

    #[Test]
    public function it_can_create_a_contact()
    {
        $contact = Contact::factory()->create();

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'internal_name' => $contact->internal_name,
        ]);

        // Check that the contact has translations attached
        $this->assertTrue($contact->translations->count() > 0);

        // Check that each translation has a label
        foreach ($contact->translations as $translation) {
            $this->assertNotNull($translation->label);
        }
    }

    #[Test]
    public function it_generates_valid_phone_numbers()
    {
        $contact = Contact::factory()->create();

        // Phone number can be null or should be a valid format
        if ($contact->phone_number) {
            $this->assertNotNull($contact->formattedPhoneNumber());
        }

        // Fax number can be null or should be a valid format
        if ($contact->fax_number) {
            $this->assertNotNull($contact->formattedFaxNumber());
        }
    }

    #[Test]
    public function it_generates_unique_internal_names()
    {
        $contacts = Contact::factory()->count(5)->create();
        $internalNames = $contacts->pluck('internal_name')->toArray();

        $this->assertEquals(count($internalNames), count(array_unique($internalNames)));
    }

    #[Test]
    public function it_properly_attaches_translations_with_labels()
    {
        $contact = Contact::factory()->create();

        foreach ($contact->translations as $translation) {
            $this->assertDatabaseHas('contact_translations', [
                'contact_id' => $contact->id,
                'language_id' => $translation->language_id,
                'label' => $translation->label,
            ]);
        }
    }

    #[Test]
    public function it_auto_loads_translations_relationship()
    {
        $contact = Contact::factory()->create();

        // Get a fresh instance of the contact from the database
        $freshContact = Contact::find($contact->id);

        // The translations relationship should be automatically loaded
        $this->assertTrue($freshContact->relationLoaded('translations'));
    }
}

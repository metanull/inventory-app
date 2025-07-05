<?php

namespace Tests\Feature\Api\Contact;

use App\Models\Contact;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateTest extends TestCase
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
    public function it_can_update_a_contact()
    {
        $contact = Contact::factory()->create([
            'internal_name' => 'original-name',
            'phone_number' => '+15555555555',
            'email' => 'original@example.com',
        ]);

        $languages = Language::all();
        $data = [
            'internal_name' => 'updated-name',
            'phone_number' => '+15555555556',
            'email' => 'updated@example.com',
            'languages' => [
                [
                    'language_id' => $languages[0]->id,
                    'label' => 'Updated Label',
                ],
            ],
        ];

        $response = $this->putJson(route('contact.update', ['contact' => $contact->id]), $data);

        $response->assertOk();
        $response->assertJsonPath('data.internal_name', 'updated-name');
        $response->assertJsonPath('data.email', 'updated@example.com');

        // Check database has updated values
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'internal_name' => 'updated-name',
            'email' => 'updated@example.com',
        ]);

        // Check that languages were updated
        $this->assertDatabaseHas('contact_language', [
            'contact_id' => $contact->id,
            'language_id' => $languages[0]->id,
            'label' => 'Updated Label',
        ]);
    }

    #[Test]
    public function it_returns_not_found_for_non_existent_contact()
    {
        $data = [
            'internal_name' => 'updated-name',
        ];

        $response = $this->putJson(route('contact.update', ['contact' => 'non-existent-id']), $data);

        $response->assertNotFound();
    }

    #[Test]
    public function it_validates_internal_name_uniqueness_on_update()
    {
        $contact1 = Contact::factory()->create(['internal_name' => 'contact-1']);
        $contact2 = Contact::factory()->create(['internal_name' => 'contact-2']);

        $data = [
            'internal_name' => 'contact-1', // Trying to use an existing name
        ];

        $response = $this->putJson(route('contact.update', ['contact' => $contact2->id]), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    #[Test]
    public function it_can_update_contact_without_changing_languages()
    {
        $contact = Contact::factory()->create([
            'internal_name' => 'original-name',
        ]);

        $originalLanguageIds = $contact->languages->pluck('id')->toArray();

        $data = [
            'internal_name' => 'updated-name',
        ];

        $response = $this->putJson(route('contact.update', ['contact' => $contact->id]), $data);

        $response->assertOk();
        $response->assertJsonPath('data.internal_name', 'updated-name');

        // Languages should remain the same
        $contact->refresh();
        $updatedLanguageIds = $contact->languages->pluck('id')->toArray();
        sort($originalLanguageIds);
        sort($updatedLanguageIds);
        $this->assertEquals($originalLanguageIds, $updatedLanguageIds);
    }
}

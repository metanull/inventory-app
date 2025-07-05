<?php

namespace Tests\Feature\Api\Contact;

use App\Models\Contact;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShowTest extends TestCase
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
    public function it_can_show_a_contact()
    {
        $contact = Contact::factory()->create();

        $response = $this->getJson(route('contact.show', ['contact' => $contact->id]));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'phone_number',
                'formatted_phone_number',
                'fax_number',
                'formatted_fax_number',
                'email',
                'languages',
                'created_at',
                'updated_at',
            ],
        ]);
        $response->assertJsonPath('data.id', $contact->id);
    }

    #[Test]
    public function it_returns_not_found_for_non_existent_contact()
    {
        $response = $this->getJson(route('contact.show', ['contact' => 'non-existent-id']));

        $response->assertNotFound();
    }

    #[Test]
    public function it_includes_language_data_in_show_response()
    {
        $contact = Contact::factory()->create();

        $response = $this->getJson(route('contact.show', ['contact' => $contact->id]));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'languages' => [
                    '*' => [
                        'id',
                        'name',
                        'label',
                    ],
                ],
            ],
        ]);
    }
}

<?php

namespace Tests\Feature\Api\Contact;

use App\Models\Contact;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexTest extends TestCase
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
    public function it_can_list_contacts()
    {
        Contact::factory()->count(5)->create();

        $response = $this->getJson(route('contact.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'internal_name',
                    'phone_number',
                    'formatted_phone_number',
                    'fax_number',
                    'formatted_fax_number',
                    'email',
                    'translations',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    #[Test]
    public function it_returns_empty_array_when_no_contacts_exist()
    {
        $response = $this->getJson(route('contact.index'));

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    #[Test]
    public function it_includes_language_data_in_response()
    {
        $contact = Contact::factory()->create();

        $response = $this->getJson(route('contact.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'translations' => [
                        '*' => [
                            'id',
                            'language_id',
                            'label',
                        ],
                    ],
                ],
            ],
        ]);
    }
}

<?php

namespace Tests\Feature\Api\Contact;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreTest extends TestCase
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
    public function it_can_create_a_contact()
    {
        $languages = Language::all();
        $data = [
            'internal_name' => 'test-contact',
            'phone_number' => '+15555555555',
            'fax_number' => '+15555555556',
            'email' => 'test@example.com',
            'languages' => [
                [
                    'language_id' => $languages[0]->id,
                    'label' => 'Contact Label 1',
                ],
                [
                    'language_id' => $languages[1]->id,
                    'label' => 'Contact Label 2',
                ],
            ],
        ];

        $response = $this->postJson(route('contact.store'), $data);

        $response->assertCreated();
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
        $response->assertJsonPath('data.internal_name', 'test-contact');
        $response->assertJsonPath('data.email', 'test@example.com');

        // Check that languages were attached
        $this->assertDatabaseHas('contact_language', [
            'contact_id' => $response->json('data.id'),
            'language_id' => $languages[0]->id,
            'label' => 'Contact Label 1',
        ]);
        $this->assertDatabaseHas('contact_language', [
            'contact_id' => $response->json('data.id'),
            'language_id' => $languages[1]->id,
            'label' => 'Contact Label 2',
        ]);
    }

    #[Test]
    public function it_requires_internal_name()
    {
        $languages = Language::all();
        $data = [
            'phone_number' => '+15555555555',
            'languages' => [
                [
                    'language_id' => $languages[0]->id,
                    'label' => 'Contact Label',
                ],
            ],
        ];

        $response = $this->postJson(route('contact.store'), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    #[Test]
    public function it_requires_at_least_one_language()
    {
        $data = [
            'internal_name' => 'test-contact',
            'phone_number' => '+15555555555',
            'languages' => [],
        ];

        $response = $this->postJson(route('contact.store'), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['languages']);
    }

    #[Test]
    public function it_validates_phone_number_format()
    {
        $languages = Language::all();
        $data = [
            'internal_name' => 'test-contact',
            'phone_number' => 'not-a-phone-number',
            'languages' => [
                [
                    'language_id' => $languages[0]->id,
                    'label' => 'Contact Label',
                ],
            ],
        ];

        $response = $this->postJson(route('contact.store'), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['phone_number']);
    }

    #[Test]
    public function it_validates_email_format()
    {
        $languages = Language::all();
        $data = [
            'internal_name' => 'test-contact',
            'email' => 'not-an-email',
            'languages' => [
                [
                    'language_id' => $languages[0]->id,
                    'label' => 'Contact Label',
                ],
            ],
        ];

        $response = $this->postJson(route('contact.store'), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['email']);
    }
}

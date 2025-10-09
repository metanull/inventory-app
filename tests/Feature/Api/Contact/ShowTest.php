<?php

namespace Tests\Feature\Api\Contact;

use App\Models\Contact;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class ShowTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createVisitorUser();
        $this->actingAs($this->user);
        Language::factory()->count(3)->create();
    }

    #[Test]
    public function test_show_returns_the_default_structure_without_relations()
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
    public function test_show_returns_the_expected_structure_with_all_relations_loaded()
    {
        $contact = Contact::factory()->create();

        $response = $this->getJson(route('contact.show', ['contact' => $contact->id, 'include' => 'translations']));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'translations' => [
                    '*' => [
                        'id',
                        'language_id',
                        'label',
                    ],
                ],
            ],
        ]);
    }
}

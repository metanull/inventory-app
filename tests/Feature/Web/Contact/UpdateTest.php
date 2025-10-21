<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Contact;

use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_update_modifies_contact_and_redirects(): void
    {
        $contact = Contact::factory()->create([
            'internal_name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        $payload = [
            'internal_name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->put(route('contacts.update', $contact), $payload);
        $response->assertRedirect();
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'internal_name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_update_validation_errors(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->put(route('contacts.update', $contact), [
            'internal_name' => '',
        ]);
        $response->assertSessionHasErrors(['internal_name']);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Contact;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class StoreTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_store_persists_contact_and_redirects(): void
    {
        $payload = [
            'internal_name' => 'Test Contact',
            'phone_number' => '+12025551234',
            'email' => 'test@example.com',
        ];

        $response = $this->post(route('contacts.store'), $payload);
        $response->assertRedirect();
        $this->assertDatabaseHas('contacts', [
            'internal_name' => 'Test Contact',
            'email' => 'test@example.com',
        ]);
    }

    public function test_store_validation_errors(): void
    {
        $response = $this->post(route('contacts.store'), [
            'internal_name' => '',
        ]);
        $response->assertSessionHasErrors(['internal_name']);
    }

    public function test_store_validates_email_format(): void
    {
        $response = $this->post(route('contacts.store'), [
            'internal_name' => 'Test',
            'email' => 'not-an-email',
        ]);
        $response->assertSessionHasErrors(['email']);
    }

    public function test_store_allows_nullable_optional_fields(): void
    {
        $payload = [
            'internal_name' => 'Minimal Contact',
        ];

        $response = $this->post(route('contacts.store'), $payload);
        $response->assertRedirect();
        $this->assertDatabaseHas('contacts', [
            'internal_name' => 'Minimal Contact',
        ]);
    }
}

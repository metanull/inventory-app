<?php

namespace Tests\Feature\Api\Contact;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function anonymous_users_cannot_list_contacts()
    {
        $response = $this->getJson(route('contact.index'));

        $response->assertUnauthorized();
    }

    #[Test]
    public function anonymous_users_cannot_view_a_contact()
    {
        $response = $this->getJson(route('contact.show', ['contact' => 'some-id']));

        $response->assertUnauthorized();
    }

    #[Test]
    public function anonymous_users_cannot_create_a_contact()
    {
        $response = $this->postJson(route('contact.store'), []);

        $response->assertUnauthorized();
    }

    #[Test]
    public function anonymous_users_cannot_update_a_contact()
    {
        $response = $this->putJson(route('contact.update', ['contact' => 'some-id']), []);

        $response->assertUnauthorized();
    }

    #[Test]
    public function anonymous_users_cannot_delete_a_contact()
    {
        $response = $this->deleteJson(route('contact.destroy', ['contact' => 'some-id']));

        $response->assertUnauthorized();
    }
}

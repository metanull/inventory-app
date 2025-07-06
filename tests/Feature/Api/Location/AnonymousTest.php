<?php

namespace Tests\Feature\Api\Location;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_anonymous_user_cannot_access_location_index(): void
    {
        $response = $this->getJson(route('location.index'));

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_access_location_show(): void
    {
        $response = $this->getJson(route('location.show', 'fake-id'));

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_create_location(): void
    {
        $response = $this->postJson(route('location.store'), []);

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_update_location(): void
    {
        $response = $this->putJson(route('location.update', 'fake-id'), []);

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_delete_location(): void
    {
        $response = $this->deleteJson(route('location.destroy', 'fake-id'));

        $response->assertUnauthorized();
    }
}

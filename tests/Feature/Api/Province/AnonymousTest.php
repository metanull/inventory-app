<?php

namespace Tests\Feature\Api\Province;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_anonymous_user_cannot_access_province_index(): void
    {
        $response = $this->getJson(route('province.index'));

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_access_province_show(): void
    {
        $response = $this->getJson(route('province.show', 'fake-id'));

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_create_province(): void
    {
        $response = $this->postJson(route('province.store'), []);

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_update_province(): void
    {
        $response = $this->putJson(route('province.update', 'fake-id'), []);

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_delete_province(): void
    {
        $response = $this->deleteJson(route('province.destroy', 'fake-id'));

        $response->assertUnauthorized();
    }
}

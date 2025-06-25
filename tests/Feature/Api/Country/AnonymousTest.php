<?php

namespace Tests\Feature\Api\Country;

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnonymousTest extends TestCase
{

    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Authentication: Assert index forbids anonymous access.
     */
    public function test_index_forbids_anonymous_access()
    {
        $response = $this->getJson(route('country.index'));

        $response->assertUnauthorized();
    }
    
    /**
     * Authentication: Assert show forbids anonymous access.
     */
    public function test_show_forbids_anonymous_access()
    {
        $country = Country::factory()->create();

        $response = $this->getJson(route('country.show', $country));

        $response->assertUnauthorized();
    }

    /**
     * Authentication: Assert store forbids anonymous access.
     */
    public function test_store_forbids_anonymous_access()
    {
        $data = Country::factory()->make()->toArray();

        $response = $this->postJson(route('country.store'), $data);

        $response->assertUnauthorized();
    }

    /**
     * Authentication: Assert update forbids anonymous access.
     */
    public function test_update_forbids_anonymous_access()
    {
        $country = Country::factory()->create();
        $data = ['internal_name' => 'Updated Name'];

        $response = $this->putJson(route('country.update', $country), $data);

        $response->assertUnauthorized();
    }

    /**
     * Authentication: Assert destroy forbids anonymous access.
     */
    public function test_destroy_forbids_anonymous_access()
    {
        $country = Country::factory()->create();

        $response = $this->deleteJson(route('country.destroy', $country));

        $response->assertUnauthorized();
    }
}
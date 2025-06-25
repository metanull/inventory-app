<?php

namespace Tests\Feature\Api\Context;

use App\Models\Context;
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
     * Authentication: index forbids anonymous access.
     */
    public function test_api_authentication_index_forbids_anonymous_access()
    {
        $response = $this->getJson(route('context.index'));
        $response->assertUnauthorized();
    }
    
    /**
     * Authentication: show forbids anonymous access.
     */
    public function test_api_authentication_show_forbids_anonymous_access()
    {
        $context = Context::factory()->create();

        $response = $this->getJson(route('context.show', $context));
        $response->assertUnauthorized();
    }

    /**
     * Authentication: store forbids anonymous access.
     */
    public function test_api_authentication_store_forbids_anonymous_access()
    {
        $data = Context::factory()->make()->except(['id','is_default']);

        $response = $this->postJson(route('context.store'), $data);
        $response->assertUnauthorized();
    }

    /**
     * Authentication: update forbids anonymous access.
     */
    public function test_api_authentication_update_forbids_anonymous_access()
    {
        $context = Context::factory()->create();

        $data = [
            'internal_name' => $this->faker->unique()->word,
            'backward_compatibility' => $this->faker->word,
        ];

        $response = $this->putJson(route('context.update', $context), $data);
        $response->assertUnauthorized();
    }

    /**
     * Authentication: destroy forbids anonymous access.
     */
    public function test_api_authentication_destroy_forbids_anonymous_access()
    {
        $context = Context::factory()->create();

        $response = $this->deleteJson(route('context.destroy', $context));
        $response->assertUnauthorized();
    }

}
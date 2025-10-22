<?php

namespace Tests\Feature\Api\Glossary;

use App\Models\Glossary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Authentication: index rejects anonymous users.
     */
    public function test_index_rejects_anonymous_users()
    {
        $response = $this->getJson(route('glossary.index'));
        $response->assertUnauthorized();
    }

    /**
     * Authentication: show rejects anonymous users.
     */
    public function test_show_rejects_anonymous_users()
    {
        $glossary = Glossary::factory()->create();

        $response = $this->getJson(route('glossary.show', $glossary));
        $response->assertUnauthorized();
    }

    /**
     * Authentication: store rejects anonymous users.
     */
    public function test_store_rejects_anonymous_users()
    {
        $data = Glossary::factory()->make()->except(['id']);

        $response = $this->postJson(route('glossary.store'), $data);
        $response->assertUnauthorized();
    }

    /**
     * Authentication: update rejects anonymous users.
     */
    public function test_update_rejects_anonymous_users()
    {
        $glossary = Glossary::factory()->create();
        $data = ['internal_name' => 'Updated Name'];

        $response = $this->patchJson(route('glossary.update', $glossary), $data);
        $response->assertUnauthorized();
    }

    /**
     * Authentication: destroy rejects anonymous users.
     */
    public function test_destroy_rejects_anonymous_users()
    {
        $glossary = Glossary::factory()->create();

        $response = $this->deleteJson(route('glossary.destroy', $glossary));
        $response->assertUnauthorized();
    }
}

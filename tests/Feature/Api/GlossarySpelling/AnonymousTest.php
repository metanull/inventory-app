<?php

namespace Tests\Feature\Api\GlossarySpelling;

use App\Models\GlossarySpelling;
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
        $response = $this->getJson(route('glossary-spelling.index'));
        $response->assertUnauthorized();
    }

    /**
     * Authentication: show rejects anonymous users.
     */
    public function test_show_rejects_anonymous_users()
    {
        $spelling = GlossarySpelling::factory()->create();

        $response = $this->getJson(route('glossary-spelling.show', $spelling));
        $response->assertUnauthorized();
    }

    /**
     * Authentication: store rejects anonymous users.
     */
    public function test_store_rejects_anonymous_users()
    {
        $data = GlossarySpelling::factory()->make()->toArray();

        $response = $this->postJson(route('glossary-spelling.store'), $data);
        $response->assertUnauthorized();
    }

    /**
     * Authentication: update rejects anonymous users.
     */
    public function test_update_rejects_anonymous_users()
    {
        $spelling = GlossarySpelling::factory()->create();
        $data = ['spelling' => 'Updated'];

        $response = $this->patchJson(route('glossary-spelling.update', $spelling), $data);
        $response->assertUnauthorized();
    }

    /**
     * Authentication: destroy rejects anonymous users.
     */
    public function test_destroy_rejects_anonymous_users()
    {
        $spelling = GlossarySpelling::factory()->create();

        $response = $this->deleteJson(route('glossary-spelling.destroy', $spelling));
        $response->assertUnauthorized();
    }
}

<?php

namespace Tests\Feature\Api\GlossaryTranslation;

use App\Models\GlossaryTranslation;
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
        $response = $this->getJson(route('glossary-translation.index'));
        $response->assertUnauthorized();
    }

    /**
     * Authentication: show rejects anonymous users.
     */
    public function test_show_rejects_anonymous_users()
    {
        $translation = GlossaryTranslation::factory()->create();

        $response = $this->getJson(route('glossary-translation.show', $translation));
        $response->assertUnauthorized();
    }

    /**
     * Authentication: store rejects anonymous users.
     */
    public function test_store_rejects_anonymous_users()
    {
        $data = GlossaryTranslation::factory()->make()->toArray();

        $response = $this->postJson(route('glossary-translation.store'), $data);
        $response->assertUnauthorized();
    }

    /**
     * Authentication: update rejects anonymous users.
     */
    public function test_update_rejects_anonymous_users()
    {
        $translation = GlossaryTranslation::factory()->create();
        $data = ['definition' => 'Updated'];

        $response = $this->patchJson(route('glossary-translation.update', $translation), $data);
        $response->assertUnauthorized();
    }

    /**
     * Authentication: destroy rejects anonymous users.
     */
    public function test_destroy_rejects_anonymous_users()
    {
        $translation = GlossaryTranslation::factory()->create();

        $response = $this->deleteJson(route('glossary-translation.destroy', $translation));
        $response->assertUnauthorized();
    }
}

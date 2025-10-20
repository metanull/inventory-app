<?php

namespace Tests\Feature\Api\CollectionTranslation;

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

    public function test_unauthenticated_user_cannot_access_collection_translation_index(): void
    {
        $response = $this->getJson(route('collection-translation.index'));
        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_access_collection_translation_show(): void
    {
        $response = $this->getJson(route('collection-translation.show', 'test-id'));
        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_create_collection_translation(): void
    {
        $response = $this->postJson(route('collection-translation.store'), []);
        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_update_collection_translation(): void
    {
        $response = $this->putJson(route('collection-translation.update', 'test-id'), []);
        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_delete_collection_translation(): void
    {
        $response = $this->deleteJson(route('collection-translation.destroy', 'test-id'));
        $response->assertUnauthorized();
    }
}

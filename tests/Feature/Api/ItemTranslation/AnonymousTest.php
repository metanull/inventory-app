<?php

namespace Tests\Feature\Api\ItemTranslation;

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

    public function test_unauthenticated_user_cannot_access_item_translation_index(): void
    {
        $response = $this->getJson(route('item-translation.index'));
        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_access_item_translation_show(): void
    {
        $response = $this->getJson(route('item-translation.show', 'test-id'));
        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_create_item_translation(): void
    {
        $response = $this->postJson(route('item-translation.store'), []);
        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_update_item_translation(): void
    {
        $response = $this->putJson(route('item-translation.update', 'test-id'), []);
        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_delete_item_translation(): void
    {
        $response = $this->deleteJson(route('item-translation.destroy', 'test-id'));
        $response->assertUnauthorized();
    }
}

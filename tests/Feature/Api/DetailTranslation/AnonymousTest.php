<?php

namespace Tests\Feature\Api\DetailTranslation;

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

    public function test_unauthenticated_user_cannot_access_detail_translation_index(): void
    {
        $response = $this->getJson(route('detail-translation.index'));
        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_access_detail_translation_show(): void
    {
        $response = $this->getJson(route('detail-translation.show', 'test-id'));
        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_create_detail_translation(): void
    {
        $response = $this->postJson(route('detail-translation.store'), []);
        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_update_detail_translation(): void
    {
        $response = $this->putJson(route('detail-translation.update', 'test-id'), []);
        $response->assertUnauthorized();
    }

    public function test_unauthenticated_user_cannot_delete_detail_translation(): void
    {
        $response = $this->deleteJson(route('detail-translation.destroy', 'test-id'));
        $response->assertUnauthorized();
    }
}

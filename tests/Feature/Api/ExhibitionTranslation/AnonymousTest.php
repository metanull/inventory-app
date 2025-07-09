<?php

namespace Tests\Feature\Api\ExhibitionTranslation;

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

    public function test_anonymous_user_cannot_access_exhibition_translation_index(): void
    {
        $response = $this->getJson(route('exhibition-translation.index'));

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_access_exhibition_translation_show(): void
    {
        $response = $this->getJson(route('exhibition-translation.show', 'test-id'));

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_create_exhibition_translation(): void
    {
        $response = $this->postJson(route('exhibition-translation.store'), []);

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_update_exhibition_translation(): void
    {
        $response = $this->putJson(route('exhibition-translation.update', 'test-id'), []);

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_delete_exhibition_translation(): void
    {
        $response = $this->deleteJson(route('exhibition-translation.destroy', 'test-id'));

        $response->assertUnauthorized();
    }
}

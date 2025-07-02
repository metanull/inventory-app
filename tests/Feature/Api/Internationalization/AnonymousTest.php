<?php

namespace Tests\Feature\Api\Internationalization;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase;

    public function test_anonymous_user_cannot_access_internationalization_index(): void
    {
        $response = $this->getJson(route('internationalization.index'));

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_create_internationalization(): void
    {
        $response = $this->postJson(route('internationalization.store'), []);

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_view_internationalization(): void
    {
        $response = $this->getJson(route('internationalization.show', 'test-id'));

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_update_internationalization(): void
    {
        $response = $this->putJson(route('internationalization.update', 'test-id'), []);

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_delete_internationalization(): void
    {
        $response = $this->deleteJson(route('internationalization.destroy', 'test-id'));

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_access_default_language_internationalizations(): void
    {
        $response = $this->getJson(route('internationalization.inDefaultLanguage'));

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_access_english_internationalizations(): void
    {
        $response = $this->getJson(route('internationalization.inEnglish'));

        $response->assertUnauthorized();
    }
}

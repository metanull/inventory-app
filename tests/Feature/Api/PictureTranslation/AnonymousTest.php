<?php

namespace Tests\Feature\Api\PictureTranslation;

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

    public function test_anonymous_user_cannot_access_picture_translations_index(): void
    {
        $response = $this->getJson(route('picture-translation.index'));
        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_access_picture_translation_show(): void
    {
        $pictureTranslation = \App\Models\PictureTranslation::factory()->create();
        $response = $this->getJson(route('picture-translation.show', $pictureTranslation->id));
        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_create_picture_translation(): void
    {
        $pictureTranslationData = \App\Models\PictureTranslation::factory()->make()->toArray();
        $response = $this->postJson(route('picture-translation.store'), $pictureTranslationData);
        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_update_picture_translation(): void
    {
        $pictureTranslation = \App\Models\PictureTranslation::factory()->create();
        $updateData = ['description' => 'Updated description'];
        $response = $this->putJson(route('picture-translation.update', $pictureTranslation->id), $updateData);
        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_delete_picture_translation(): void
    {
        $pictureTranslation = \App\Models\PictureTranslation::factory()->create();
        $response = $this->deleteJson(route('picture-translation.destroy', $pictureTranslation->id));
        $response->assertUnauthorized();
    }
}

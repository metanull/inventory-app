<?php

namespace Tests\Feature\Api\PictureTranslation;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_authenticated_user_can_access_picture_translations_index(): void
    {
        $response = $this->getJson(route('picture-translation.index'));
        $response->assertOk();
    }

    public function test_picture_translations_index_returns_correct_structure(): void
    {
        \App\Models\PictureTranslation::factory()->count(3)->create();

        $response = $this->getJson('/api/picture-translation');
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'picture_id',
                    'language_id',
                    'context_id',
                    'description',
                    'caption',
                    'author_id',
                    'text_copy_editor_id',
                    'translator_id',
                    'translation_copy_editor_id',
                    'backward_compatibility',
                    'extra',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    public function test_picture_translations_index_returns_empty_when_no_translations(): void
    {
        $response = $this->getJson('/api/picture-translation');
        $response->assertOk();
        $response->assertJsonPath('data', []);
    }

    public function test_picture_translations_index_returns_paginated_results(): void
    {
        \App\Models\PictureTranslation::factory()->count(20)->create();

        $response = $this->getJson('/api/picture-translation');
        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'links',
            'meta',
        ]);
    }
}

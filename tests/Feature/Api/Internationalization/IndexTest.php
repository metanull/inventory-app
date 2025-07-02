<?php

namespace Tests\Feature\Api\Internationalization;

use App\Models\Internationalization;
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

    public function test_authenticated_user_can_view_internationalizations_index(): void
    {
        $internationalizations = Internationalization::factory(3)->create();

        $response = $this->getJson(route('internationalization.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'contextualization_id',
                        'language_id',
                        'name',
                        'alternate_name',
                        'description',
                        'type',
                        'holder',
                        'owner',
                        'initial_owner',
                        'dates',
                        'location',
                        'dimensions',
                        'place_of_production',
                        'method_for_datation',
                        'method_for_provenance',
                        'obtention',
                        'bibliography',
                        'extra',
                        'author_id',
                        'text_copy_editor_id',
                        'translator_id',
                        'translation_copy_editor_id',
                        'backward_compatibility',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links',
                'meta',
            ]);

        foreach ($internationalizations as $internationalization) {
            $response->assertJsonPath('data.*.id', fn ($ids) => in_array($internationalization->id, $ids));
        }
    }

    public function test_index_returns_paginated_results(): void
    {
        Internationalization::factory(25)->create();

        $response = $this->getJson(route('internationalization.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'links',
                    'path',
                    'per_page',
                    'to',
                    'total',
                ],
            ]);

        $this->assertEquals(15, count($response->json('data')));
        $this->assertEquals(25, $response->json('meta.total'));
    }

    public function test_index_includes_relationships_in_response(): void
    {
        $internationalization = Internationalization::factory()->create();

        $response = $this->getJson(route('internationalization.index'));

        $response->assertOk()
            ->assertJsonPath('data.0.contextualization', fn ($contextualization) => $contextualization !== null)
            ->assertJsonPath('data.0.language', fn ($language) => $language !== null);
    }

    public function test_index_returns_empty_when_no_internationalizations(): void
    {
        $response = $this->getJson(route('internationalization.index'));

        $response->assertOk()
            ->assertJsonPath('data', [])
            ->assertJsonPath('meta.total', 0);
    }
}

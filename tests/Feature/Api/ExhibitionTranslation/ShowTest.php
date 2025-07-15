<?php

namespace Tests\Feature\Api\ExhibitionTranslation;

use App\Models\ExhibitionTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_show_exhibition_translation(): void
    {
        $translation = ExhibitionTranslation::factory()->create();

        $response = $this->getJson(route('exhibition-translation.show', ['exhibition_translation' => $translation->id]));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'exhibition_id',
                    'language_id',
                    'context_id',
                    'title',
                    'description',
                    'url',
                    'backward_compatibility',
                    'extra',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.id', $translation->id)
            ->assertJsonPath('data.exhibition_id', $translation->exhibition_id)
            ->assertJsonPath('data.language_id', $translation->language_id)
            ->assertJsonPath('data.context_id', $translation->context_id)
            ->assertJsonPath('data.title', $translation->title);
    }

    public function test_show_includes_relationship_data(): void
    {
        $translation = ExhibitionTranslation::factory()->create();

        $response = $this->getJson(route('exhibition-translation.show', ['exhibition_translation' => $translation->id]));

        $response->assertOk();

        $responseData = $response->json('data');

        // Check that relationship fields are present (if included in resource)
        $this->assertArrayHasKey('exhibition_id', $responseData);
        $this->assertArrayHasKey('language_id', $responseData);
        $this->assertArrayHasKey('context_id', $responseData);
    }

    public function test_show_returns_not_found_for_non_existent_exhibition_translation(): void
    {
        $response = $this->getJson(route('exhibition-translation.show', ['exhibition_translation' => 'non-existent-id']));

        $response->assertNotFound();
    }

    public function test_show_exhibition_translation_with_url(): void
    {
        $translation = ExhibitionTranslation::factory()->withUrl()->create();

        $response = $this->getJson(route('exhibition-translation.show', ['exhibition_translation' => $translation->id]));

        $response->assertOk()
            ->assertJsonPath('data.url', $translation->url);
    }

    public function test_show_exhibition_translation_with_extra_data(): void
    {
        $extraData = ['custom_field' => 'custom_value', 'another_field' => 'another_value'];
        $translation = ExhibitionTranslation::factory()->withExtra($extraData)->create();

        $response = $this->getJson(route('exhibition-translation.show', ['exhibition_translation' => $translation->id]));

        $response->assertOk()
            ->assertJsonPath('data.extra', $extraData);
    }
}

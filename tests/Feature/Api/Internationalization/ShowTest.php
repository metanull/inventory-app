<?php

namespace Tests\Feature\Api\Internationalization;

use App\Models\Internationalization;
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

    public function test_authenticated_user_can_view_internationalization(): void
    {
        $internationalization = Internationalization::factory()->create();

        $response = $this->getJson(route('internationalization.show', $internationalization->id));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
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
                    'contextualization',
                    'language',
                ],
            ])
            ->assertJsonPath('data.id', $internationalization->id)
            ->assertJsonPath('data.name', $internationalization->name)
            ->assertJsonPath('data.description', $internationalization->description);
    }

    public function test_show_includes_relationships(): void
    {
        $internationalization = Internationalization::factory()->create();

        $response = $this->getJson(route('internationalization.show', $internationalization->id));

        $response->assertOk()
            ->assertJsonPath('data.contextualization.id', $internationalization->contextualization_id)
            ->assertJsonPath('data.language.id', $internationalization->language_id);
    }

    public function test_show_returns_404_for_nonexistent_internationalization(): void
    {
        $nonExistentId = $this->faker->uuid();

        $response = $this->getJson(route('internationalization.show', $nonExistentId));

        $response->assertNotFound();
    }

    public function test_show_returns_404_for_invalid_uuid(): void
    {
        $response = $this->getJson(route('internationalization.show', 'invalid-uuid'));

        $response->assertNotFound();
    }

    public function test_show_includes_author_relationships_when_present(): void
    {
        $internationalization = Internationalization::factory()->create();

        $response = $this->getJson(route('internationalization.show', $internationalization->id));

        $response->assertOk();

        // If author relationships exist, they should be included
        if ($internationalization->author_id) {
            $response->assertJsonPath('data.author.id', $internationalization->author_id);
        }
        if ($internationalization->text_copy_editor_id) {
            $response->assertJsonPath('data.text_copy_editor.id', $internationalization->text_copy_editor_id);
        }
        if ($internationalization->translator_id) {
            $response->assertJsonPath('data.translator.id', $internationalization->translator_id);
        }
        if ($internationalization->translation_copy_editor_id) {
            $response->assertJsonPath('data.translation_copy_editor.id', $internationalization->translation_copy_editor_id);
        }
    }
}

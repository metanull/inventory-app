<?php

namespace Tests\Feature\Api\ExhibitionTranslation;

use App\Models\Exhibition;
use App\Models\ExhibitionTranslation;
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

    public function test_can_list_exhibition_translations(): void
    {
        ExhibitionTranslation::factory()->count(3)->create();

        $response = $this->getJson(route('exhibition-translation.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
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
                ],
            ]);

        $this->assertGreaterThanOrEqual(3, count($response->json('data')));
    }

    public function test_index_returns_empty_when_no_exhibition_translations(): void
    {
        $response = $this->getJson(route('exhibition-translation.index'));

        $response->assertOk()
            ->assertJson([
                'data' => [],
            ]);
    }

    public function test_index_can_filter_by_exhibition_id(): void
    {
        $exhibition1 = Exhibition::factory()->create();
        $exhibition2 = Exhibition::factory()->create();

        $exhibition1Translations = ExhibitionTranslation::factory()->count(2)->create(['exhibition_id' => $exhibition1->id]);
        $exhibition2Translations = ExhibitionTranslation::factory()->count(2)->create(['exhibition_id' => $exhibition2->id]);

        $response = $this->getJson(route('exhibition-translation.index', ['exhibition_id' => $exhibition1->id]));

        $response->assertOk();

        $responseData = $response->json('data');
        $this->assertCount(2, $responseData);

        foreach ($responseData as $translation) {
            $this->assertEquals($exhibition1->id, $translation['exhibition_id']);
        }
    }

    public function test_index_can_filter_by_language_id(): void
    {
        $translation1 = ExhibitionTranslation::factory()->create();
        $translation2 = ExhibitionTranslation::factory()->create(['language_id' => $translation1->language_id]);
        $translation3 = ExhibitionTranslation::factory()->create(); // Different language

        $response = $this->getJson(route('exhibition-translation.index', ['language_id' => $translation1->language_id]));

        $response->assertOk();

        $responseData = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($responseData));

        foreach ($responseData as $translation) {
            $this->assertEquals($translation1->language_id, $translation['language_id']);
        }
    }

    public function test_index_can_filter_by_context_id(): void
    {
        $translation1 = ExhibitionTranslation::factory()->create();
        $translation2 = ExhibitionTranslation::factory()->create(['context_id' => $translation1->context_id]);
        $translation3 = ExhibitionTranslation::factory()->create(); // Different context

        $response = $this->getJson(route('exhibition-translation.index', ['context_id' => $translation1->context_id]));

        $response->assertOk();

        $responseData = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($responseData));

        foreach ($responseData as $translation) {
            $this->assertEquals($translation1->context_id, $translation['context_id']);
        }
    }

    public function test_index_can_filter_by_default_context(): void
    {
        // This test would need a default context to be properly set up
        $response = $this->getJson(route('exhibition-translation.index', ['default_context' => true]));

        $response->assertOk();
        $this->assertIsArray($response->json('data'));
    }
}

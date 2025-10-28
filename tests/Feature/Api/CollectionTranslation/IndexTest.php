<?php

namespace Tests\Feature\Api\CollectionTranslation;

use App\Models\Collection;
use App\Models\CollectionTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class IndexTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createVisitorUser();
        $this->actingAs($this->user);
    }

    public function test_can_list_collection_translations(): void
    {
        CollectionTranslation::factory()->count(3)->create();

        $response = $this->getJson(route('collection-translation.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'collection_id',
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

    public function test_index_returns_empty_when_no_collection_translations(): void
    {
        $response = $this->getJson(route('collection-translation.index'));

        $response->assertOk()
            ->assertJson([
                'data' => [],
            ]);
    }

    public function test_index_can_filter_by_collection_id(): void
    {
        $collection1 = Collection::factory()->create();
        $collection2 = Collection::factory()->create();

        $collection1Translations = CollectionTranslation::factory()->count(2)->forCollection($collection1->id)->create();
        $collection2Translations = CollectionTranslation::factory()->count(2)->forCollection($collection2->id)->create();

        $response = $this->getJson(route('collection-translation.index', ['collection_id' => $collection1->id]));

        $response->assertOk();

        $responseData = $response->json('data');
        $this->assertCount(2, $responseData);

        foreach ($responseData as $translation) {
            $this->assertEquals($collection1->id, $translation['collection_id']);
        }
    }

    public function test_index_can_filter_by_language_id(): void
    {
        $translation1 = CollectionTranslation::factory()->create();
        $translation2 = CollectionTranslation::factory()->withLanguage($translation1->language_id)->create();
        $translation3 = CollectionTranslation::factory()->create(); // Different language

        $response = $this->getJson(route('collection-translation.index', ['language_id' => $translation1->language_id]));

        $response->assertOk();

        $responseData = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($responseData));

        foreach ($responseData as $translation) {
            $this->assertEquals($translation1->language_id, $translation['language_id']);
        }
    }

    public function test_index_can_filter_by_context_id(): void
    {
        $translation1 = CollectionTranslation::factory()->create();
        $translation2 = CollectionTranslation::factory()->withContext($translation1->context_id)->create();
        $translation3 = CollectionTranslation::factory()->create(); // Different context

        $response = $this->getJson(route('collection-translation.index', ['context_id' => $translation1->context_id]));

        $response->assertOk();

        $responseData = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($responseData));

        foreach ($responseData as $translation) {
            $this->assertEquals($translation1->context_id, $translation['context_id']);
        }
    }
}

<?php

namespace Tests\Feature\Api\ItemTranslation;

use App\Models\Item;
use App\Models\ItemTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class IndexTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createVisitorUser();
        $this->actingAs($this->user);
    }

    public function test_can_list_item_translations(): void
    {
        ItemTranslation::factory()->count(3)->create();

        $response = $this->getJson(route('item-translation.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'item_id',
                        'language_id',
                        'context_id',
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

        $this->assertGreaterThanOrEqual(3, count($response->json('data')));
    }

    public function test_index_returns_empty_when_no_item_translations(): void
    {
        $response = $this->getJson(route('item-translation.index'));

        $response->assertOk()
            ->assertJson([
                'data' => [],
            ]);
    }

    public function test_index_can_filter_by_item_id(): void
    {
        $item1 = Item::factory()->withoutTranslations()->create();
        $item2 = Item::factory()->withoutTranslations()->create();

        $item1Translations = ItemTranslation::factory()->count(2)->forItem($item1->id)->create();
        $item2Translations = ItemTranslation::factory()->count(2)->forItem($item2->id)->create();

        $response = $this->getJson(route('item-translation.index', ['item_id' => $item1->id]));

        $response->assertOk();

        $responseData = $response->json('data');
        $this->assertCount(2, $responseData);

        foreach ($responseData as $translation) {
            $this->assertEquals($item1->id, $translation['item_id']);
        }
    }

    public function test_index_can_filter_by_language_id(): void
    {
        $translation1 = ItemTranslation::factory()->create();
        $translation2 = ItemTranslation::factory()->forLanguage($translation1->language_id)->create();
        $translation3 = ItemTranslation::factory()->create(); // Different language

        $response = $this->getJson(route('item-translation.index', ['language_id' => $translation1->language_id]));

        $response->assertOk();

        $responseData = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($responseData));

        foreach ($responseData as $translation) {
            $this->assertEquals($translation1->language_id, $translation['language_id']);
        }
    }

    public function test_index_can_filter_by_context_id(): void
    {
        $translation1 = ItemTranslation::factory()->create();
        $translation2 = ItemTranslation::factory()->forContext($translation1->context_id)->create();
        $translation3 = ItemTranslation::factory()->create(); // Different context

        $response = $this->getJson(route('item-translation.index', ['context_id' => $translation1->context_id]));

        $response->assertOk();

        $responseData = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($responseData));

        foreach ($responseData as $translation) {
            $this->assertEquals($translation1->context_id, $translation['context_id']);
        }
    }
}

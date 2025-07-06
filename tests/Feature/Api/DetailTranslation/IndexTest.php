<?php

namespace Tests\Feature\Api\DetailTranslation;

use App\Models\Detail;
use App\Models\DetailTranslation;
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

    public function test_can_list_detail_translations(): void
    {
        DetailTranslation::factory()->count(3)->create();

        $response = $this->getJson(route('detail-translation.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'detail_id',
                        'language_id',
                        'context_id',
                        'name',
                        'alternate_name',
                        'description',
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

    public function test_index_returns_empty_when_no_detail_translations(): void
    {
        $response = $this->getJson(route('detail-translation.index'));

        $response->assertOk()
            ->assertJson([
                'data' => [],
            ]);
    }

    public function test_index_can_filter_by_detail_id(): void
    {
        $detail1 = Detail::factory()->withoutTranslations()->create();
        $detail2 = Detail::factory()->withoutTranslations()->create();

        $detail1Translations = DetailTranslation::factory()->count(2)->forDetail($detail1->id)->create();
        $detail2Translations = DetailTranslation::factory()->count(2)->forDetail($detail2->id)->create();

        $response = $this->getJson(route('detail-translation.index', ['detail_id' => $detail1->id]));

        $response->assertOk();

        $responseData = $response->json('data');
        $this->assertCount(2, $responseData);

        foreach ($responseData as $translation) {
            $this->assertEquals($detail1->id, $translation['detail_id']);
        }
    }

    public function test_index_can_filter_by_language_id(): void
    {
        $translation1 = DetailTranslation::factory()->create();
        $translation2 = DetailTranslation::factory()->forLanguage($translation1->language_id)->create();
        $translation3 = DetailTranslation::factory()->create(); // Different language

        $response = $this->getJson(route('detail-translation.index', ['language_id' => $translation1->language_id]));

        $response->assertOk();

        $responseData = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($responseData));

        foreach ($responseData as $translation) {
            $this->assertEquals($translation1->language_id, $translation['language_id']);
        }
    }

    public function test_index_can_filter_by_context_id(): void
    {
        $translation1 = DetailTranslation::factory()->create();
        $translation2 = DetailTranslation::factory()->forContext($translation1->context_id)->create();
        $translation3 = DetailTranslation::factory()->create(); // Different context

        $response = $this->getJson(route('detail-translation.index', ['context_id' => $translation1->context_id]));

        $response->assertOk();

        $responseData = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($responseData));

        foreach ($responseData as $translation) {
            $this->assertEquals($translation1->context_id, $translation['context_id']);
        }
    }
}

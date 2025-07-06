<?php

namespace Tests\Feature\Api\Province;

use App\Models\Country;
use App\Models\Language;
use App\Models\Province;
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

    public function test_can_get_empty_province_list(): void
    {
        $response = $this->getJson(route('province.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data',
            ])
            ->assertJsonPath('data', []);
    }

    public function test_can_get_province_list_with_data(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();
        $provinces = Province::factory(3)->create();

        $response = $this->getJson(route('province.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'internal_name',
                        'country_id',
                        'languages' => [
                            '*' => [
                                'id',
                                'name',
                                'translated_name',
                            ],
                        ],
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_province_list_includes_languages_relationship(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();
        $province = Province::factory()->create();

        $response = $this->getJson(route('province.index'));

        $response->assertOk();

        $provinceData = collect($response->json('data'))->firstWhere('id', $province->id);
        $this->assertNotNull($provinceData);
        $this->assertArrayHasKey('languages', $provinceData);
        $this->assertGreaterThan(0, count($provinceData['languages']));

        foreach ($provinceData['languages'] as $language) {
            $this->assertArrayHasKey('id', $language);
            $this->assertArrayHasKey('name', $language);
            $this->assertArrayHasKey('translated_name', $language);
        }
    }
}

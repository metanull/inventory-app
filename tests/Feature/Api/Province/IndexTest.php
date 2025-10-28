<?php

namespace Tests\Feature\Api\Province;

use App\Models\Country;
use App\Models\Language;
use App\Models\Province;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class IndexTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createVisitorUser();
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
                        'translations' => [
                            '*' => [
                                'id',
                                'language_id',
                                'name',
                            ],
                        ],
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_province_list_includes_translations_relationship(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();
        $province = Province::factory()->create();

        $response = $this->getJson(route('province.index'));

        $response->assertOk();

        $provinceData = collect($response->json('data'))->firstWhere('id', $province->id);
        $this->assertNotNull($provinceData);
        $this->assertArrayHasKey('translations', $provinceData);
        $this->assertGreaterThan(0, count($provinceData['translations']));

        foreach ($provinceData['translations'] as $translation) {
            $this->assertArrayHasKey('id', $translation);
            $this->assertArrayHasKey('language_id', $translation);
            $this->assertArrayHasKey('name', $translation);
        }
    }
}

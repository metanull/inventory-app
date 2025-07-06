<?php

namespace Tests\Feature\Api\Province;

use App\Models\Country;
use App\Models\Language;
use App\Models\Province;
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

    public function test_can_show_province(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();
        $province = Province::factory()->create();

        $response = $this->getJson(route('province.show', $province));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
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
            ])
            ->assertJsonPath('data.id', $province->id)
            ->assertJsonPath('data.internal_name', $province->internal_name)
            ->assertJsonPath('data.country_id', $province->country_id);
    }

    public function test_province_show_includes_languages_relationship(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();
        $province = Province::factory()->create();

        $response = $this->getJson(route('province.show', $province));

        $response->assertOk();

        $provinceData = $response->json('data');
        $this->assertArrayHasKey('languages', $provinceData);
        $this->assertGreaterThan(0, count($provinceData['languages']));

        foreach ($provinceData['languages'] as $language) {
            $this->assertArrayHasKey('id', $language);
            $this->assertArrayHasKey('name', $language);
            $this->assertArrayHasKey('translated_name', $language);
        }
    }

    public function test_shows_404_for_nonexistent_province(): void
    {
        $response = $this->getJson(route('province.show', 'non-existent-id'));

        $response->assertNotFound();
    }
}

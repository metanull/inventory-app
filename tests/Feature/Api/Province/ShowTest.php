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
            ])
            ->assertJsonPath('data.id', $province->id)
            ->assertJsonPath('data.internal_name', $province->internal_name)
            ->assertJsonPath('data.country_id', $province->country_id);
    }

    public function test_province_show_includes_translations_relationship(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();
        $province = Province::factory()->create();

        $response = $this->getJson(route('province.show', $province));

        $response->assertOk();

        $provinceData = $response->json('data');
        $this->assertArrayHasKey('translations', $provinceData);
        $this->assertGreaterThan(0, count($provinceData['translations']));

        foreach ($provinceData['translations'] as $translation) {
            $this->assertArrayHasKey('id', $translation);
            $this->assertArrayHasKey('language_id', $translation);
            $this->assertArrayHasKey('name', $translation);
        }
    }

    public function test_shows_404_for_nonexistent_province(): void
    {
        $response = $this->getJson(route('province.show', 'non-existent-id'));

        $response->assertNotFound();
    }
}

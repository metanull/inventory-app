<?php

namespace Tests\Feature\Api\Province;

use App\Models\Country;
use App\Models\Language;
use App\Models\Province;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class StoreTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_can_create_province(): void
    {
        $languages = Language::factory(2)->create();
        $country = Country::factory()->create();

        $provinceData = Province::factory()->make(['country_id' => $country->id])->toArray();
        $provinceData['translations'] = [
            [
                'language_id' => $languages[0]->id,
                'name' => $this->faker->words(2, true),
            ],
            [
                'language_id' => $languages[1]->id,
                'name' => $this->faker->words(2, true),
            ],
        ];

        $response = $this->postJson(route('province.store'), $provinceData);

        $response->assertCreated()
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
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.internal_name', $provinceData['internal_name'])
            ->assertJsonPath('data.country_id', $provinceData['country_id']);

        $this->assertDatabaseHas('provinces', [
            'internal_name' => $provinceData['internal_name'],
            'country_id' => $provinceData['country_id'],
        ]);

        // Check language relationships
        foreach ($provinceData['translations'] as $translationData) {
            $this->assertDatabaseHas('province_translations', [
                'language_id' => $translationData['language_id'],
                'name' => $translationData['name'],
            ]);
        }
    }

    public function test_cannot_create_province_without_required_fields(): void
    {
        $response = $this->postJson(route('province.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['internal_name', 'country_id', 'translations']);
    }

    public function test_cannot_create_province_with_invalid_country(): void
    {
        $languages = Language::factory(1)->create();

        $provinceData = Province::factory()->make()->toArray();
        $provinceData['country_id'] = 'invalid-country-id';
        $provinceData['translations'] = [
            [
                'language_id' => $languages[0]->id,
                'name' => $this->faker->words(2, true),
            ],
        ];

        $response = $this->postJson(route('province.store'), $provinceData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['country_id']);
    }

    public function test_cannot_create_province_with_invalid_translations(): void
    {
        $country = Country::factory()->create();

        $provinceData = Province::factory()->make(['country_id' => $country->id])->toArray();
        $provinceData['translations'] = [
            [
                'language_id' => 'invalid-language-id',
                'name' => $this->faker->words(2, true),
            ],
        ];

        $response = $this->postJson(route('province.store'), $provinceData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['translations.0.language_id']);
    }

    public function test_cannot_create_province_with_duplicate_internal_name(): void
    {
        Language::factory(2)->create();
        $country = Country::factory()->create();

        $existingProvince = Province::factory()->create(['country_id' => $country->id]);
        $languages = Language::factory(1)->create();

        $provinceData = Province::factory()->make(['country_id' => $country->id])->toArray();
        $provinceData['internal_name'] = $existingProvince->internal_name;
        $provinceData['translations'] = [
            [
                'language_id' => $languages[0]->id,
                'name' => $this->faker->words(2, true),
            ],
        ];

        $response = $this->postJson(route('province.store'), $provinceData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['internal_name']);
    }

    public function test_cannot_create_province_without_translations(): void
    {
        $country = Country::factory()->create();

        $provinceData = Province::factory()->make(['country_id' => $country->id])->toArray();
        $provinceData['translations'] = [];

        $response = $this->postJson(route('province.store'), $provinceData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['translations']);
    }
}

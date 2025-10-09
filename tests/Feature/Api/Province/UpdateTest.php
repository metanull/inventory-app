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

class UpdateTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_can_update_province(): void
    {
        $languages = Language::factory(3)->create();
        $country = Country::factory()->create();
        $newCountry = Country::factory()->create();

        $province = Province::factory()->create(['country_id' => $country->id]);

        $updateData = [
            'internal_name' => $this->faker->unique()->words(2, true),
            'country_id' => $newCountry->id,
            'translations' => [
                [
                    'language_id' => $languages[0]->id,
                    'name' => $this->faker->words(2, true),
                ],
                [
                    'language_id' => $languages[1]->id,
                    'name' => $this->faker->words(2, true),
                ],
            ],
        ];

        $response = $this->putJson(route('province.update', $province), $updateData);

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
            ->assertJsonPath('data.internal_name', $updateData['internal_name'])
            ->assertJsonPath('data.country_id', $updateData['country_id']);

        $this->assertDatabaseHas('provinces', [
            'id' => $province->id,
            'internal_name' => $updateData['internal_name'],
            'country_id' => $updateData['country_id'],
        ]);

        // Check translation relationships were updated
        foreach ($updateData['translations'] as $translationData) {
            $this->assertDatabaseHas('province_translations', [
                'province_id' => $province->id,
                'language_id' => $translationData['language_id'],
                'name' => $translationData['name'],
            ]);
        }
    }

    public function test_can_update_province_without_translations(): void
    {
        Language::factory(3)->create();
        $country = Country::factory()->create();
        $newCountry = Country::factory()->create();

        $province = Province::factory()->create(['country_id' => $country->id]);

        $updateData = [
            'internal_name' => $this->faker->unique()->words(2, true),
            'country_id' => $newCountry->id,
        ];

        $response = $this->putJson(route('province.update', $province), $updateData);

        $response->assertOk()
            ->assertJsonPath('data.internal_name', $updateData['internal_name'])
            ->assertJsonPath('data.country_id', $updateData['country_id']);

        $this->assertDatabaseHas('provinces', [
            'id' => $province->id,
            'internal_name' => $updateData['internal_name'],
            'country_id' => $updateData['country_id'],
        ]);
    }

    public function test_cannot_update_province_without_required_fields(): void
    {
        Language::factory(3)->create();
        $country = Country::factory()->create();
        $province = Province::factory()->create(['country_id' => $country->id]);

        $response = $this->putJson(route('province.update', $province), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['internal_name', 'country_id']);
    }

    public function test_cannot_update_province_with_invalid_country(): void
    {
        Language::factory(3)->create();
        $country = Country::factory()->create();
        $province = Province::factory()->create(['country_id' => $country->id]);

        $updateData = [
            'internal_name' => $this->faker->unique()->words(2, true),
            'country_id' => 'invalid-country-id',
        ];

        $response = $this->putJson(route('province.update', $province), $updateData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['country_id']);
    }

    public function test_cannot_update_province_with_duplicate_internal_name(): void
    {
        Language::factory(3)->create();
        $country = Country::factory()->create();
        $province1 = Province::factory()->create(['country_id' => $country->id]);
        $province2 = Province::factory()->create(['country_id' => $country->id]);

        $updateData = [
            'internal_name' => $province1->internal_name,
            'country_id' => $country->id,
        ];

        $response = $this->putJson(route('province.update', $province2), $updateData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['internal_name']);
    }

    public function test_shows_404_for_nonexistent_province(): void
    {
        $updateData = [
            'internal_name' => $this->faker->unique()->words(2, true),
            'country_id' => Country::factory()->create()->id,
        ];

        $response = $this->putJson(route('province.update', 'non-existent-id'), $updateData);

        $response->assertNotFound();
    }
}

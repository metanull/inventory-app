<?php

namespace Tests\Feature\Api\Location;

use App\Models\Country;
use App\Models\Language;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_update_location(): void
    {
        $languages = Language::factory(3)->create();
        $country = Country::factory()->create();
        $newCountry = Country::factory()->create();

        $Location = Location::factory()->create(['country_id' => $country->id]);

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

        $response = $this->putJson(route('location.update', $Location), $updateData);

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
            ->assertJsonPath('data.id', $Location->id)
            ->assertJsonPath('data.internal_name', $updateData['internal_name'])
            ->assertJsonPath('data.country_id', $updateData['country_id']);

        $this->assertDatabaseHas('Locations', [
            'id' => $Location->id,
            'internal_name' => $updateData['internal_name'],
            'country_id' => $updateData['country_id'],
        ]);

        // Check translation relationships were updated
        foreach ($updateData['translations'] as $translationData) {
            $this->assertDatabaseHas('location_translations', [
                'location_id' => $Location->id,
                'language_id' => $translationData['language_id'],
                'name' => $translationData['name'],
            ]);
        }
    }

    public function test_can_update_location_without_translations(): void
    {
        Language::factory(3)->create();
        $country = Country::factory()->create();
        $newCountry = Country::factory()->create();

        $Location = Location::factory()->create(['country_id' => $country->id]);

        $updateData = [
            'internal_name' => $this->faker->unique()->words(2, true),
            'country_id' => $newCountry->id,
        ];

        $response = $this->putJson(route('location.update', $Location), $updateData);

        $response->assertOk()
            ->assertJsonPath('data.internal_name', $updateData['internal_name'])
            ->assertJsonPath('data.country_id', $updateData['country_id']);

        $this->assertDatabaseHas('Locations', [
            'id' => $Location->id,
            'internal_name' => $updateData['internal_name'],
            'country_id' => $updateData['country_id'],
        ]);
    }

    public function test_cannot_update_location_without_required_fields(): void
    {
        Language::factory(3)->create();
        $country = Country::factory()->create();
        $Location = Location::factory()->create(['country_id' => $country->id]);

        $response = $this->putJson(route('location.update', $Location), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['internal_name', 'country_id']);
    }

    public function test_cannot_update_location_with_invalid_country(): void
    {
        Language::factory(3)->create();
        $country = Country::factory()->create();
        $Location = Location::factory()->create(['country_id' => $country->id]);

        $updateData = [
            'internal_name' => $this->faker->unique()->words(2, true),
            'country_id' => 'invalid-country-id',
        ];

        $response = $this->putJson(route('location.update', $Location), $updateData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['country_id']);
    }

    public function test_cannot_update_location_with_duplicate_internal_name(): void
    {
        Language::factory(3)->create();
        $country = Country::factory()->create();
        $Location1 = Location::factory()->create(['country_id' => $country->id]);
        $Location2 = Location::factory()->create(['country_id' => $country->id]);

        $updateData = [
            'internal_name' => $Location1->internal_name,
            'country_id' => $country->id,
        ];

        $response = $this->putJson(route('location.update', $Location2), $updateData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['internal_name']);
    }

    public function test_shows_404_for_nonexistent_location(): void
    {
        $updateData = [
            'internal_name' => $this->faker->unique()->words(2, true),
            'country_id' => Country::factory()->create()->id,
        ];

        $response = $this->putJson(route('location.update', 'non-existent-id'), $updateData);

        $response->assertNotFound();
    }
}

<?php

namespace Tests\Feature\Api\Location;

use App\Models\Country;
use App\Models\Language;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_create_location(): void
    {
        $languages = Language::factory(2)->create();
        $country = Country::factory()->create();

        $LocationData = Location::factory()->make(['country_id' => $country->id])->toArray();
        $LocationData['languages'] = [
            [
                'language_id' => $languages[0]->id,
                'name' => $this->faker->words(2, true),
            ],
            [
                'language_id' => $languages[1]->id,
                'name' => $this->faker->words(2, true),
            ],
        ];

        $response = $this->postJson(route('location.store'), $LocationData);

        $response->assertCreated()
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
            ->assertJsonPath('data.internal_name', $LocationData['internal_name'])
            ->assertJsonPath('data.country_id', $LocationData['country_id']);

        $this->assertDatabaseHas('Locations', [
            'internal_name' => $LocationData['internal_name'],
            'country_id' => $LocationData['country_id'],
        ]);

        // Check language relationships
        foreach ($LocationData['languages'] as $languageData) {
            $this->assertDatabaseHas('Location_language', [
                'language_id' => $languageData['language_id'],
                'name' => $languageData['name'],
            ]);
        }
    }

    public function test_cannot_create_location_without_required_fields(): void
    {
        $response = $this->postJson(route('location.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['internal_name', 'country_id', 'languages']);
    }

    public function test_cannot_create_location_with_invalid_country(): void
    {
        $languages = Language::factory(1)->create();

        $LocationData = Location::factory()->make()->toArray();
        $LocationData['country_id'] = 'invalid-country-id';
        $LocationData['languages'] = [
            [
                'language_id' => $languages[0]->id,
                'name' => $this->faker->words(2, true),
            ],
        ];

        $response = $this->postJson(route('location.store'), $LocationData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['country_id']);
    }

    public function test_cannot_create_location_with_invalid_languages(): void
    {
        $country = Country::factory()->create();

        $LocationData = Location::factory()->make(['country_id' => $country->id])->toArray();
        $LocationData['languages'] = [
            [
                'language_id' => 'invalid-language-id',
                'name' => $this->faker->words(2, true),
            ],
        ];

        $response = $this->postJson(route('location.store'), $LocationData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['languages.0.language_id']);
    }

    public function test_cannot_create_location_with_duplicate_internal_name(): void
    {
        Language::factory(2)->create();
        $country = Country::factory()->create();

        $existingLocation = Location::factory()->create(['country_id' => $country->id]);
        $languages = Language::factory(1)->create();

        $LocationData = Location::factory()->make(['country_id' => $country->id])->toArray();
        $LocationData['internal_name'] = $existingLocation->internal_name;
        $LocationData['languages'] = [
            [
                'language_id' => $languages[0]->id,
                'name' => $this->faker->words(2, true),
            ],
        ];

        $response = $this->postJson(route('location.store'), $LocationData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['internal_name']);
    }

    public function test_cannot_create_location_without_languages(): void
    {
        $country = Country::factory()->create();

        $LocationData = Location::factory()->make(['country_id' => $country->id])->toArray();
        $LocationData['languages'] = [];

        $response = $this->postJson(route('location.store'), $LocationData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['languages']);
    }
}

<?php

namespace Tests\Feature\Api\Location;

use App\Enums\Permission;
use App\Models\Country;
use App\Models\Language;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class StoreTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith(Permission::dataOperations());
        $this->actingAs($this->user);
    }

    public function test_can_create_location(): void
    {
        $languages = Language::factory(2)->create();
        $country = Country::factory()->create();

        $LocationData = Location::factory()->make(['country_id' => $country->id])->toArray();
        $LocationData['translations'] = [
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
            ->assertJsonPath('data.internal_name', $LocationData['internal_name'])
            ->assertJsonPath('data.country_id', $LocationData['country_id']);

        $this->assertDatabaseHas('Locations', [
            'internal_name' => $LocationData['internal_name'],
            'country_id' => $LocationData['country_id'],
        ]);

        // Check translation relationships
        foreach ($LocationData['translations'] as $translationData) {
            $this->assertDatabaseHas('location_translations', [
                'language_id' => $translationData['language_id'],
                'name' => $translationData['name'],
            ]);
        }
    }

    public function test_cannot_create_location_without_required_fields(): void
    {
        $response = $this->postJson(route('location.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['internal_name', 'country_id', 'translations']);
    }

    public function test_cannot_create_location_with_invalid_country(): void
    {
        $languages = Language::factory(1)->create();

        $LocationData = Location::factory()->make()->toArray();
        $LocationData['country_id'] = 'invalid-country-id';
        $LocationData['translations'] = [
            [
                'language_id' => $languages[0]->id,
                'name' => $this->faker->words(2, true),
            ],
        ];

        $response = $this->postJson(route('location.store'), $LocationData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['country_id']);
    }

    public function test_cannot_create_location_with_invalid_translations(): void
    {
        $country = Country::factory()->create();

        $LocationData = Location::factory()->make(['country_id' => $country->id])->toArray();
        $LocationData['translations'] = [
            [
                'language_id' => 'invalid-language-id',
                'name' => $this->faker->words(2, true),
            ],
        ];

        $response = $this->postJson(route('location.store'), $LocationData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['translations.0.language_id']);
    }

    public function test_cannot_create_location_with_duplicate_internal_name(): void
    {
        Language::factory(2)->create();
        $country = Country::factory()->create();

        $existingLocation = Location::factory()->create(['country_id' => $country->id]);
        $languages = Language::factory(1)->create();

        $LocationData = Location::factory()->make(['country_id' => $country->id])->toArray();
        $LocationData['internal_name'] = $existingLocation->internal_name;
        $LocationData['translations'] = [
            [
                'language_id' => $languages[0]->id,
                'name' => $this->faker->words(2, true),
            ],
        ];

        $response = $this->postJson(route('location.store'), $LocationData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['internal_name']);
    }

    public function test_cannot_create_location_without_translations(): void
    {
        $country = Country::factory()->create();

        $LocationData = Location::factory()->make(['country_id' => $country->id])->toArray();
        $LocationData['translations'] = [];

        $response = $this->postJson(route('location.store'), $LocationData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['translations']);
    }
}

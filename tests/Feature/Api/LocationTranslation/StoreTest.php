<?php

namespace Tests\Feature\Api\LocationTranslation;

use App\Models\Language;
use App\Models\Location;
use App\Models\LocationTranslation;
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

    public function test_can_store_location_translation(): void
    {
        $location = Location::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();
        $data = LocationTranslation::factory()->make([
            'location_id' => $location->id,
            'language_id' => $language->id,
        ])->toArray();

        $response = $this->postJson(route('location-translation.store'), $data);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'location_id',
                    'language_id',
                    'name',
                    'description',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('location_translations', [
            'location_id' => $data['location_id'],
            'language_id' => $data['language_id'],
            'name' => $data['name'],
        ]);
    }

    public function test_store_requires_location_id(): void
    {
        $language = Language::factory()->create();
        $data = LocationTranslation::factory()->make([
            'language_id' => $language->id,
        ])->except(['location_id']);

        $response = $this->postJson(route('location-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['location_id']);
    }

    public function test_store_requires_language_id(): void
    {
        $location = Location::factory()->withoutTranslations()->create();
        $data = LocationTranslation::factory()->make([
            'location_id' => $location->id,
        ])->except(['language_id']);

        $response = $this->postJson(route('location-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_requires_name(): void
    {
        $location = Location::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();
        $data = LocationTranslation::factory()->make([
            'location_id' => $location->id,
            'language_id' => $language->id,
        ])->except(['name']);

        $response = $this->postJson(route('location-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_allows_null_description(): void
    {
        $location = Location::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();
        $data = LocationTranslation::factory()->make([
            'location_id' => $location->id,
            'language_id' => $language->id,
            'description' => null,
        ])->toArray();

        $response = $this->postJson(route('location-translation.store'), $data);

        $response->assertCreated();
        $this->assertDatabaseHas('location_translations', [
            'location_id' => $data['location_id'],
            'language_id' => $data['language_id'],
            'description' => null,
        ]);
    }
}

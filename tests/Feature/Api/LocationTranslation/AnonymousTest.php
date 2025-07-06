<?php

namespace Tests\Feature\Api\LocationTranslation;

use App\Models\LocationTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_anonymous_user_cannot_access_index(): void
    {
        $response = $this->getJson(route('location-translation.index'));

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_access_show(): void
    {
        $location = \App\Models\Location::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $locationTranslation = LocationTranslation::factory()->create([
            'location_id' => $location->id,
            'language_id' => $language->id,
        ]);

        $response = $this->getJson(route('location-translation.show', ['location_translation' => $locationTranslation->id]));

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_store(): void
    {
        $location = \App\Models\Location::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $data = LocationTranslation::factory()->make([
            'location_id' => $location->id,
            'language_id' => $language->id,
        ])->toArray();

        $response = $this->postJson(route('location-translation.store'), $data);

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_update(): void
    {
        $location = \App\Models\Location::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $locationTranslation = LocationTranslation::factory()->create([
            'location_id' => $location->id,
            'language_id' => $language->id,
        ]);
        $data = ['name' => 'Updated Location'];

        $response = $this->putJson(route('location-translation.update', ['location_translation' => $locationTranslation->id]), $data);

        $response->assertUnauthorized();
    }

    public function test_anonymous_user_cannot_destroy(): void
    {
        $location = \App\Models\Location::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $locationTranslation = LocationTranslation::factory()->create([
            'location_id' => $location->id,
            'language_id' => $language->id,
        ]);

        $response = $this->deleteJson(route('location-translation.destroy', ['location_translation' => $locationTranslation->id]));

        $response->assertUnauthorized();
    }
}

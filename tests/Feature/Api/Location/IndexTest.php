<?php

namespace Tests\Feature\Api\Location;

use App\Models\Country;
use App\Models\Language;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_get_empty_location_list(): void
    {
        $response = $this->getJson(route('location.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data',
            ])
            ->assertJsonPath('data', []);
    }

    public function test_can_get_location_list_with_data(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();
        $Locations = Location::factory(3)->create();

        $response = $this->getJson(route('location.index'));

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

    public function test_location_list_includes_translations_relationship(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();
        $Location = Location::factory()->create();

        $response = $this->getJson(route('location.index'));

        $response->assertOk();

        $LocationData = collect($response->json('data'))->firstWhere('id', $Location->id);
        $this->assertNotNull($LocationData);
        $this->assertArrayHasKey('translations', $LocationData);
        $this->assertGreaterThan(0, count($LocationData['translations']));

        foreach ($LocationData['translations'] as $translation) {
            $this->assertArrayHasKey('id', $translation);
            $this->assertArrayHasKey('language_id', $translation);
            $this->assertArrayHasKey('name', $translation);
        }
    }
}

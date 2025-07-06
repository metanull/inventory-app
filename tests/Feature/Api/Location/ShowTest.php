<?php

namespace Tests\Feature\Api\Location;

use App\Models\Country;
use App\Models\Language;
use App\Models\Location;
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

    public function test_can_show_location(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();
        $Location = Location::factory()->create();

        $response = $this->getJson(route('location.show', $Location));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                    'country_id',
                    'translations' => [
                        '*' => [
                            'id',
                            'location_id',
                            'language_id',
                            'name',
                            'description',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.id', $Location->id)
            ->assertJsonPath('data.internal_name', $Location->internal_name)
            ->assertJsonPath('data.country_id', $Location->country_id);
    }

    public function test_location_show_includes_translations_relationship(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();
        $Location = Location::factory()->create();

        $response = $this->getJson(route('location.show', $Location));

        $response->assertOk();

        $LocationData = $response->json('data');
        $this->assertArrayHasKey('translations', $LocationData);
        $this->assertGreaterThan(0, count($LocationData['translations']));

        foreach ($LocationData['translations'] as $translation) {
            $this->assertArrayHasKey('id', $translation);
            $this->assertArrayHasKey('location_id', $translation);
            $this->assertArrayHasKey('language_id', $translation);
            $this->assertArrayHasKey('name', $translation);
            $this->assertArrayHasKey('description', $translation);
        }
    }

    public function test_shows_404_for_nonexistent_location(): void
    {
        $response = $this->getJson(route('location.show', 'non-existent-id'));

        $response->assertNotFound();
    }
}

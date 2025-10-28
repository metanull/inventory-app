<?php

namespace Tests\Feature\Api\LocationTranslation;

use App\Enums\Permission;
use App\Models\LocationTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class UpdateTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith(Permission::dataOperations());
        $this->actingAs($this->user);
    }

    public function test_can_update_location_translation(): void
    {
        $locationTranslation = LocationTranslation::factory()->create();
        $data = [
            'name' => $this->faker->city,
            'description' => $this->faker->paragraph,
        ];

        $response = $this->putJson(route('location-translation.update', ['location_translation' => $locationTranslation->id]), $data);

        $response->assertOk()
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
            'id' => $locationTranslation->id,
            'name' => $data['name'],
            'description' => $data['description'],
        ]);
    }

    public function test_update_requires_name(): void
    {
        $locationTranslation = LocationTranslation::factory()->create();
        $data = ['name' => ''];

        $response = $this->putJson(route('location-translation.update', ['location_translation' => $locationTranslation->id]), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_allows_null_description(): void
    {
        $locationTranslation = LocationTranslation::factory()->create();
        $data = [
            'name' => $this->faker->city,
            'description' => null,
        ];

        $response = $this->putJson(route('location-translation.update', ['location_translation' => $locationTranslation->id]), $data);

        $response->assertOk();
        $this->assertDatabaseHas('location_translations', [
            'id' => $locationTranslation->id,
            'name' => $data['name'],
            'description' => null,
        ]);
    }

    public function test_update_returns_not_found_for_non_existent_location_translation(): void
    {
        $data = [
            'name' => $this->faker->city,
        ];

        $response = $this->putJson(route('location-translation.update', ['location_translation' => 'non-existent-id']), $data);

        $response->assertNotFound();
    }
}

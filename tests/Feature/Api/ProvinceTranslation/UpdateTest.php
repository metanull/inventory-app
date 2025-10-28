<?php

namespace Tests\Feature\Api\ProvinceTranslation;

use App\Enums\Permission;
use App\Models\ProvinceTranslation;
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

    public function test_can_update_province_translation(): void
    {
        $provinceTranslation = ProvinceTranslation::factory()->create();
        $data = [
            'name' => $this->faker->state,
            'description' => $this->faker->paragraph,
        ];

        $response = $this->putJson(route('province-translation.update', ['province_translation' => $provinceTranslation->id]), $data);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'province_id',
                    'language_id',
                    'name',
                    'description',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('province_translations', [
            'id' => $provinceTranslation->id,
            'name' => $data['name'],
            'description' => $data['description'],
        ]);
    }

    public function test_update_requires_name(): void
    {
        $provinceTranslation = ProvinceTranslation::factory()->create();
        $data = ['name' => ''];

        $response = $this->putJson(route('province-translation.update', ['province_translation' => $provinceTranslation->id]), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_update_allows_null_description(): void
    {
        $provinceTranslation = ProvinceTranslation::factory()->create();
        $data = [
            'name' => $this->faker->state,
            'description' => null,
        ];

        $response = $this->putJson(route('province-translation.update', ['province_translation' => $provinceTranslation->id]), $data);

        $response->assertOk();
        $this->assertDatabaseHas('province_translations', [
            'id' => $provinceTranslation->id,
            'name' => $data['name'],
            'description' => null,
        ]);
    }

    public function test_update_returns_not_found_for_non_existent_province_translation(): void
    {
        $data = [
            'name' => $this->faker->state,
        ];

        $response = $this->putJson(route('province-translation.update', ['province_translation' => 'non-existent-id']), $data);

        $response->assertNotFound();
    }
}

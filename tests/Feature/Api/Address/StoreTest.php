<?php

namespace Tests\Feature\Api\Address;

use App\Models\Country;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class StoreTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_can_create_address(): void
    {
        Language::factory(3)->create();
        $country = Country::factory()->create();

        $data = [
            'internal_name' => 'test-address',
            'country_id' => $country->id,
            'translations' => [
                [
                    'language_id' => Language::first()->id,
                    'address' => 'Test Address',
                    'description' => 'Test Description',
                ],
            ],
        ];

        $response = $this->postJson(route('address.store'), $data);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                    'country_id',
                    'translations',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('addresses', [
            'internal_name' => $data['internal_name'],
            'country_id' => $data['country_id'],
        ]);
    }

    public function test_cannot_create_address_without_required_fields(): void
    {
        $response = $this->postJson(route('address.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'internal_name',
                'country_id',
            ]);
    }

    public function test_cannot_create_address_with_invalid_country(): void
    {
        Language::factory(3)->create();

        $data = [
            'internal_name' => 'test-address',
            'country_id' => 'invalid',
            'translations' => [
                [
                    'language_id' => Language::first()->id,
                    'address' => 'Test Address',
                    'description' => 'Test Description',
                ],
            ],
        ];

        $response = $this->postJson(route('address.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['country_id']);
    }

    public function test_cannot_set_id_manually(): void
    {
        Language::factory(3)->create();
        $country = Country::factory()->create();

        $data = [
            'id' => 'custom-id',
            'internal_name' => 'test-address',
            'country_id' => $country->id,
            'translations' => [
                [
                    'language_id' => Language::first()->id,
                    'address' => 'Test Address',
                    'description' => 'Test Description',
                ],
            ],
        ];

        $response = $this->postJson(route('address.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['id']);
    }
}

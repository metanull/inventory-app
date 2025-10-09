<?php

namespace Tests\Feature\Api\Address;

use App\Models\Address;
use App\Models\Country;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class UpdateTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_can_update_address(): void
    {
        Language::factory(3)->create();
        Country::factory(3)->create();
        $address = Address::factory()->create();

        $data = [
            'internal_name' => 'updated-address',
            'country_id' => Country::first()->id,
            'translations' => [
                [
                    'language_id' => Language::first()->id,
                    'address' => 'Updated Address',
                    'description' => 'Updated Description',
                ],
            ],
        ];

        $response = $this->putJson(route('address.update', $address), $data);

        $response->assertOk()
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
            'id' => $address->id,
            'internal_name' => $data['internal_name'],
        ]);
    }

    public function test_update_returns_404_for_nonexistent_address(): void
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

        $response = $this->putJson(route('address.update', 'nonexistent-id'), $data);

        $response->assertNotFound();
    }

    public function test_cannot_update_address_without_required_fields(): void
    {
        Language::factory(3)->create();
        Country::factory(3)->create();
        $address = Address::factory()->create();

        $response = $this->putJson(route('address.update', $address), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'internal_name',
                'country_id',
            ]);
    }

    public function test_cannot_update_address_with_invalid_country(): void
    {
        Language::factory(3)->create();
        Country::factory(3)->create();
        $address = Address::factory()->create();

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

        $response = $this->putJson(route('address.update', $address), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['country_id']);
    }

    public function test_cannot_update_address_id(): void
    {
        Language::factory(3)->create();
        Country::factory(3)->create();
        $address = Address::factory()->create();

        $data = [
            'id' => 'new-id',
            'internal_name' => 'test-address',
            'country_id' => Country::first()->id,
            'translations' => [
                [
                    'language_id' => Language::first()->id,
                    'address' => 'Test Address',
                    'description' => 'Test Description',
                ],
            ],
        ];

        $response = $this->putJson(route('address.update', $address), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['id']);
    }

    public function test_can_update_address_internal_name(): void
    {
        Language::factory(3)->create();
        Country::factory(3)->create();
        $address = Address::factory()->create();

        $newInternalName = 'updated-internal-name';
        $data = [
            'internal_name' => $newInternalName,
            'country_id' => $address->country_id,
            'translations' => [
                [
                    'language_id' => Language::first()->id,
                    'address' => 'Test Address',
                    'description' => 'Test Description',
                ],
            ],
        ];

        $response = $this->putJson(route('address.update', $address), $data);

        $response->assertOk();

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'internal_name' => $newInternalName,
        ]);
    }
}

<?php

namespace Tests\Feature\Api\AddressTranslation;

use App\Models\AddressTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_update_address_translation(): void
    {
        $address = \App\Models\Address::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $addressTranslation = AddressTranslation::factory()->create([
            'address_id' => $address->id,
            'language_id' => $language->id,
        ]);
        $data = [
            'address' => $this->faker->streetAddress,
            'description' => $this->faker->paragraph,
        ];

        $response = $this->putJson(route('address-translation.update', ['address_translation' => $addressTranslation->id]), $data);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'address_id',
                    'language_id',
                    'address',
                    'description',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('address_translations', [
            'id' => $addressTranslation->id,
            'address' => $data['address'],
            'description' => $data['description'],
        ]);
    }

    public function test_update_requires_address(): void
    {
        $address = \App\Models\Address::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $addressTranslation = AddressTranslation::factory()->create([
            'address_id' => $address->id,
            'language_id' => $language->id,
        ]);
        $data = ['address' => ''];

        $response = $this->putJson(route('address-translation.update', ['address_translation' => $addressTranslation->id]), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['address']);
    }

    public function test_update_allows_null_description(): void
    {
        $address = \App\Models\Address::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $addressTranslation = AddressTranslation::factory()->create([
            'address_id' => $address->id,
            'language_id' => $language->id,
        ]);
        $data = [
            'address' => $this->faker->streetAddress,
            'description' => null,
        ];

        $response = $this->putJson(route('address-translation.update', ['address_translation' => $addressTranslation->id]), $data);

        $response->assertOk();
        $this->assertDatabaseHas('address_translations', [
            'id' => $addressTranslation->id,
            'address' => $data['address'],
            'description' => null,
        ]);
    }

    public function test_update_returns_not_found_for_non_existent_address_translation(): void
    {
        $data = [
            'address' => $this->faker->streetAddress,
        ];

        $response = $this->putJson(route('address-translation.update', ['address_translation' => 'non-existent-id']), $data);

        $response->assertNotFound();
    }
}

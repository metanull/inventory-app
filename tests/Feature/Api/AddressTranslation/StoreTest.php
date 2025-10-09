<?php

namespace Tests\Feature\Api\AddressTranslation;

use App\Models\Address;
use App\Models\AddressTranslation;
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

    public function test_can_store_address_translation(): void
    {
        $address = Address::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();

        $data = AddressTranslation::factory()->make([
            'address_id' => $address->id,
            'language_id' => $language->id,
        ])->toArray();

        $response = $this->postJson(route('address-translation.store'), $data);

        $response->assertCreated()
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
            'address_id' => $data['address_id'],
            'language_id' => $data['language_id'],
            'address' => $data['address'],
        ]);
    }

    public function test_store_requires_address_id(): void
    {
        $language = Language::factory()->create();
        $data = AddressTranslation::factory()->make([
            'language_id' => $language->id,
        ])->except(['address_id']);

        $response = $this->postJson(route('address-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['address_id']);
    }

    public function test_store_requires_language_id(): void
    {
        $address = Address::factory()->withoutTranslations()->create();
        $data = AddressTranslation::factory()->make([
            'address_id' => $address->id,
        ])->except(['language_id']);

        $response = $this->postJson(route('address-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_requires_address(): void
    {
        $address = Address::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();
        $data = AddressTranslation::factory()->make([
            'address_id' => $address->id,
            'language_id' => $language->id,
        ])->except(['address']);

        $response = $this->postJson(route('address-translation.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['address']);
    }

    public function test_store_allows_null_description(): void
    {
        $address = Address::factory()->withoutTranslations()->create();
        $language = Language::factory()->create();

        $data = AddressTranslation::factory()->make([
            'address_id' => $address->id,
            'language_id' => $language->id,
            'description' => null,
        ])->toArray();

        $response = $this->postJson(route('address-translation.store'), $data);

        $response->assertCreated();
        $this->assertDatabaseHas('address_translations', [
            'address_id' => $data['address_id'],
            'language_id' => $data['language_id'],
            'description' => null,
        ]);
    }
}

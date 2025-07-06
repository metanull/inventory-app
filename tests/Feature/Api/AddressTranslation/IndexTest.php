<?php

namespace Tests\Feature\Api\AddressTranslation;

use App\Models\Address;
use App\Models\AddressTranslation;
use App\Models\Language;
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

    public function test_can_list_address_translations(): void
    {
        // Create enough languages to avoid conflicts
        Language::factory(10)->create();

        // Create 3 separate addresses to ensure different combinations
        $address1 = Address::factory()->create();
        $address2 = Address::factory()->create();
        $address3 = Address::factory()->create();

        // Get available languages for each address
        $lang1 = Language::whereNotIn('id', $address1->translations()->pluck('language_id'))->first();
        $lang2 = Language::whereNotIn('id', $address2->translations()->pluck('language_id'))->first();
        $lang3 = Language::whereNotIn('id', $address3->translations()->pluck('language_id'))->first();

        // Create specific translations
        $translations = [
            AddressTranslation::factory()->create(['address_id' => $address1->id, 'language_id' => $lang1->id]),
            AddressTranslation::factory()->create(['address_id' => $address2->id, 'language_id' => $lang2->id]),
            AddressTranslation::factory()->create(['address_id' => $address3->id, 'language_id' => $lang3->id]),
        ];

        $response = $this->getJson(route('address-translation.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'address_id',
                        'language_id',
                        'address',
                        'description',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);

        // We should have at least our 3 additional translations plus any created by the Address factory
        $this->assertGreaterThanOrEqual(6, count($response->json('data')));
    }

    public function test_index_returns_empty_when_no_address_translations(): void
    {
        $response = $this->getJson(route('address-translation.index'));

        $response->assertOk()
            ->assertJson([
                'data' => [],
            ]);
    }
}

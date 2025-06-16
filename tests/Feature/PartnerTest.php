<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Country;
use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PartnerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_index_requires_authentication(): void
    {
        $response_anonymous = $this->getJson(route('partner.index'));
        $response_anonymous->assertUnauthorized();

        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->getJson(route('partner.index'));
        $response_authenticated->assertOk();
    }

    public function test_show_requires_authentication(): void
    {
        $partner = Partner::factory()->withCountry()->create();

        $response_anonymous = $this->getJson(route('partner.show', $partner->id));
        $response_anonymous->assertUnauthorized();

        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->getJson(route('partner.show', $partner->id));
        $response_authenticated->assertOk();
    }

    public function test_store_requires_authentication(): void
    {
        $response_anonymous = $this->postJson(route('partner.store'), [
            'internal_name' => 'Test Partner',
            'backward_compatibility' => 'TP',
            'country_id' => Country::factory()->create()->id,
            'type' => 'museum',
        ]);
        $response_anonymous->assertUnauthorized();

        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->postJson(route('partner.store'), [
                'internal_name' => 'Test Partner',
                'backward_compatibility' => 'TP',
                'country_id' => Country::factory()->create()->id,
                'type' => 'museum',
            ]);
        $response_authenticated->assertCreated();
    }
    public function test_update_requires_authentication(): void
    {
        $partner = Partner::factory()->withCountry()->create();

        $response_anonymous = $this->putJson(route('partner.update', $partner->id), [
            'internal_name' => 'Updated Partner',
            'backward_compatibility' => 'UP',
            'country_id' => Country::factory()->create()->id,
            'type' => 'museum',
        ]);
        $response_anonymous->assertUnauthorized();

        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->putJson(route('partner.update', $partner->id), [
                'internal_name' => 'Updated Partner',
                'backward_compatibility' => 'UP',
                'country_id' => Country::factory()->create()->id,
                'type' => 'museum',
            ]);
        $response_authenticated->assertOk();
    }
    public function test_destroy_requires_authentication(): void
    {
        $partner = Partner::factory()->withCountry()->create();

        $response_anonymous = $this->deleteJson(route('partner.destroy', $partner->id));
        $response_anonymous->assertUnauthorized();

        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->deleteJson(route('partner.destroy', $partner->id));
        $response_authenticated->assertNoContent();
    }

    public function test_show_returns_a_well_structured_response(): void
    {
        $user = User::factory()->create();
        $partner = Partner::factory()->withCountry()->create();
        $response = $this->actingAs($user)
            ->getJson(route('partner.show', $partner->id));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                    'backward_compatibility',
                    'type',
                    'country' => [
                        'id',
                        'internal_name',
                        'backward_compatibility',
                    ],
                ],
            ])
            ->assertJsonFragment([
                'id' => $partner->id,
                'internal_name' => $partner->internal_name,
                'backward_compatibility' => $partner->backward_compatibility,
                'type' => $partner->type,
            ])
            ->assertJsonFragment([
                'id' => $partner->country->id,
            ]);
    }

    public function test_index_returns_a_well_structured_response(): void
    {
        $user = User::factory()->create();
        $partner = Partner::factory()->withCountry()->create();
        $response = $this->actingAs($user)
            ->getJson(route('partner.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'internal_name',
                        'backward_compatibility',
                        'type',
                        'country' => [
                            'id',
                            'internal_name',
                            'backward_compatibility',
                        ],
                    ],
                ],
            ])
            ->assertJsonFragment([
                'id' => $partner->id,
                'internal_name' => $partner->internal_name,
                'backward_compatibility' => $partner->backward_compatibility,
                'type' => $partner->type,
            ]);
    }

    public function test_store_returns_a_well_structured_response(): void
    {
        $user = User::factory()->create();
        $country = Country::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('partner.store'), [
                'internal_name' => 'Test Partner',
                'backward_compatibility' => 'TP',
                'country_id' => $country->id,
                'type' => 'museum',
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                    'backward_compatibility',
                    'type',
                    'country' => [
                        'id',
                        'internal_name',
                        'backward_compatibility',
                    ],
                ],
            ])
            ->assertJsonFragment([
                'internal_name' => 'Test Partner',
                'backward_compatibility' => 'TP',
                'type' => 'museum',
            ])
            ->assertJsonFragment([
                'id' => $country->id,
                'internal_name' => $country->internal_name,
                'backward_compatibility' => $country->backward_compatibility,
            ]);
    }

    public function test_update_returns_a_well_structured_response(): void
    {
        $user = User::factory()->create();
        $partner = Partner::factory()->withCountry()->create();
        $other_country = Country::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('partner.update', $partner->id), [
                'internal_name' => 'Updated Partner',
                'backward_compatibility' => 'UP',
                'country_id' => $other_country->id,
                'type' => 'museum',
            ]);
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                    'backward_compatibility',
                    'type',
                    'country' => [
                        'id',
                        'internal_name',
                        'backward_compatibility',
                    ],
                ],
            ])
            ->assertJsonFragment([
                'id' => $partner->id,
                'internal_name' => 'Updated Partner',
                'backward_compatibility' => 'UP',
                'type' => 'museum',
            ])
            ->assertJsonFragment([
                'id' => $other_country->id,
                'internal_name' => $other_country->internal_name,
                'backward_compatibility' => $other_country->backward_compatibility,
            ]);
    }

    public function test_destroy_returns_no_content(): void
    {
        $user = User::factory()->create();
        $partner = Partner::factory()->withCountry()->create();

        $response = $this->actingAs($user)
            ->deleteJson(route('partner.destroy', $partner->id));

        $response->assertNoContent();
        $this->assertDatabaseMissing('partners', ['id' => $partner->id]);
    }

    public function test_index_returns_empty_response_when_no_data(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('partner.index'));

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_show_returns_not_found_response_when_not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('partner.show', 'nonexistent'));

        $response->assertNotFound();
    }

    public function test_update_returns_not_found_response_when_not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->putJson(route('partner.update', 'nonexistent'), [
                'internal_name' => 'Updated Partner',
                'backward_compatibility' => 'UP',
                'country_id' => Country::factory()->create()->id,
                'type' => 'museum',
            ]);

        $response->assertNotFound();
    }

    public function test_destroy_returns_not_found_response_when_not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->deleteJson(route('partner.destroy', 'nonexistent'));

        $response->assertNotFound();
    }

    public function test_store_returns_unprocessable_and_adequate_validation_errors(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('partner.store'), [
                'id' => 'invalid-id', // Invalid: prohibited field
                'internal_name' => '', // Invalid: required field
                'backward_compatibility' => 'TP',
                'country_id' => 'invalid_id', // Invalid: not a valid country ID
                'type' => 'invalid_type', // Invalid: not in allowed types
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['id','internal_name', 'country_id', 'type']);
    }

    public function test_update_returns_unprocessable_and_adequate_validation_errors(): void
    {
        $user = User::factory()->create();
        $partner = Partner::factory()->withCountry()->create();

        $response = $this->actingAs($user)
            ->putJson(route('partner.update', $partner->id), [
                'id' => 'invalid-id', // Invalid: prohibited field
                'internal_name' => '', // Invalid: required field
                'backward_compatibility' => 'UP',
                'country_id' => 'invalid_id', // Invalid: not a valid country ID
                'type' => 'invalid_type', // Invalid: not in allowed types
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['id','internal_name', 'country_id', 'type']);
    }

    public function test_store_partner_as_museum_creates_a_partner(): void
    {
        $user = User::factory()->create();
        $country = Country::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('partner.store'), [
                'internal_name' => 'Test Museum Partner',
                'backward_compatibility' => 'TM',
                'country_id' => $country->id,
                'type' => 'museum',
            ]);

        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'internal_name' => 'Test Museum Partner',
                    'backward_compatibility' => 'TM',
                    'type' => 'museum',
                    'country' => [
                        'id' => $country->id,
                        'internal_name' => $country->internal_name,
                        'backward_compatibility' => $country->backward_compatibility,
                    ],
                ],
            ])
            ->assertJson([
                'data' => [
                    'country' => [
                        'id' => $country->id,
                        'internal_name' => $country->internal_name,
                        'backward_compatibility' => $country->backward_compatibility,
                    ],
                ],
            ]);
    }

    public function test_store_partner_as_institution_creates_a_partner(): void
    {
        $user = User::factory()->create();
        $country = Country::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('partner.store'), [
                'internal_name' => 'Test Institution Partner',
                'backward_compatibility' => 'TI',
                'country_id' => $country->id,
                'type' => 'institution',
            ]);

        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'internal_name' => 'Test Institution Partner',
                    'backward_compatibility' => 'TI',
                    'type' => 'institution',
                    'country' => [
                        'id' => $country->id,
                        'internal_name' => $country->internal_name,
                        'backward_compatibility' => $country->backward_compatibility,
                    ],
                ],
            ])
            ->assertJson([
                'data' => [
                    'country' => [
                        'id' => $country->id,
                        'internal_name' => $country->internal_name,
                        'backward_compatibility' => $country->backward_compatibility,
                    ],
                ],
            ]);
    }

    public function test_store_partner_as_individual_creates_a_partner(): void
    {
        $user = User::factory()->create();
        $country = Country::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('partner.store'), [
                'internal_name' => 'Test Individual Partner',
                'backward_compatibility' => 'TD',
                'country_id' => $country->id,
                'type' => 'individual',
            ]);

        $response->assertCreated()
            ->assertJson([
                'data' => [
                    'internal_name' => 'Test Individual Partner',
                    'backward_compatibility' => 'TD',
                    'type' => 'individual',
                    'country' => [
                        'id' => $country->id,
                        'internal_name' => $country->internal_name,
                        'backward_compatibility' => $country->backward_compatibility,
                    ],
                ],
            ])
            ->assertJson([
                'data' => [
                    'country' => [
                        'id' => $country->id,
                        'internal_name' => $country->internal_name,
                        'backward_compatibility' => $country->backward_compatibility,
                    ],
                ],
            ]);
    }

    public function test_store_partner_as_invalid_type_returns_unprocessable(): void
    {
        $user = User::factory()->create();
        $country = Country::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('partner.store'), [
                'internal_name' => 'Test Invalid Partner',
                'backward_compatibility' => 'TX',
                'country_id' => $country->id,
                'type' => 'invalid_type',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['type'])
            ->assertJsonFragment([
                'message' => 'The selected type is invalid.',
            ]);
    }
    
}

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

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_the_application_returns_a_successful_response_as_a_user(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->get(route('partner.index'));

        $response->assertStatus(200);
    }

    public function test_partner_creation_museum(): void
    {
        $user = User::factory()->create();
        $country = Country::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('partner.store'), [
                'internal_name' => 'Test Partner',
                'backward_compatibility' => 'TT',
                'country_id' => $country->id,
                'type' => 'museum',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'internal_name' => 'Test Partner',
                'backward_compatibility' => 'TT',
                'type' => 'museum',
                // 'country_id' => $country->id,
            ]);
    }

    public function test_partner_creation_institution(): void
    {
        $user = User::factory()->create();
        $country = Country::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('partner.store'), [
                'internal_name' => 'Test Partner',
                'backward_compatibility' => 'TT',
                'country_id' => $country->id,
                'type' => 'institution',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'internal_name' => 'Test Partner',
                'backward_compatibility' => 'TT',
                'type' => 'institution',
                // 'country_id' => $country->id,
            ]);
    }

    public function test_partner_creation_individual(): void
    {
        $user = User::factory()->create();
        $country = Country::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('partner.store'), [
                'internal_name' => 'Test Partner',
                'backward_compatibility' => 'TT',
                'country_id' => $country->id,
                'type' => 'individual',
            ]);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'internal_name' => 'Test Partner',
                'backward_compatibility' => 'TT',
                'type' => 'individual',
                // 'country_id' => $country->id,
            ]);
    }
/*
    public function test_partner_update(): void
    {
        $user = User::factory()->create();
        $partner = Partner::factory()->Museum()->create([
            'country_id' => Country::factory()->create(),
        ]);
        $other_country = Country::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('partner.update', $partner->id), [
                'internal_name' => 'Updated Test Partner',
                'backward_compatibility' => 'UU',
                'country_id' => $other_country->id,
                'type' => 'institution',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'internal_name' => 'Updated Test Partner',
                    'backward_compatibility' => 'UU',
                    'type' => 'institution',
                ],
            ])
            ->assertJsonFragment([
                //'data' => [
                //    'country' => [
                //        //'id' => $other_country->id,
                //        'id' => $partner->country_id,
                //    ],
                //],
                //// 'id' => $partner->country_id,
                'id' => $other_country->id,
            ]);
    }
*/
    public function test_partner_deletion(): void
    {
        $user = User::factory()->create();
        $partner = Partner::factory()->Museum()->create([
            'country_id' => Country::factory()->create(),
        ]);

        $response = $this->actingAs($user)
            ->deleteJson(route('partner.destroy', $partner->id));

        $response->assertStatus(204);
        $this->assertDatabaseMissing('partners', ['id' => $partner->id]);
    }

    public function test_partner_retrieval(): void
    {
        $user = User::factory()->create();
        $partner = Partner::factory()->Museum()->create([
            'country_id' => Country::factory()->create(),
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('partner.show', $partner->id));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'internal_name' => $partner->internal_name,
                    'country' => ['id' => $partner->country_id ],
                    'type' => $partner->type,
                    'id' => $partner->id
                ],
            ]);
    }

    public function test_partner_retrieval__not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('partner.show', 'nonexistent'));

        $response->assertStatus(404);
    }

    public function test_partner_list_retrieval(): void
    {
        $user = User::factory()->create();
        $partners = Partner::factory()->count(3)->Museum()->create();

        $response = $this->actingAs($user)
            ->getJson(route('partner.index'));

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonFragment([
                'id' => $partners[0]->id,
                'internal_name' => $partners[0]->internal_name,
                'backward_compatibility' => $partners[0]->backward_compatibility,
                'type' => $partners[0]->type,
                'id' => $partners[0]->country_id,
            ]);
    }

    public function test_partner_list_retrieval__empty(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('partner.index'));

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }
}

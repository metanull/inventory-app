<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CountryTest extends TestCase
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
            ->get(route('country.index'));

        $response->assertStatus(200);
    }

    public function test_country_creation(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('country.store'), [
                'id' => 'TST',
                'internal_name' => 'Test Country',
                'backward_compatibility' => 'TT',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'id' => 'TST',
                    'internal_name' => 'Test Country',
                    'backward_compatibility' => 'TT',
                ],
            ]);
    }

    public function test_country_update(): void
    {
        $user = User::factory()->create();
        $country = \App\Models\Country::factory()->create([
            'id' => 'TST',
            'internal_name' => 'Test Country',
            'backward_compatibility' => 'TT',
        ]);

        $response = $this->actingAs($user)
            ->putJson(route('country.update', $country->id), [
                'internal_name' => 'Updated Test Country',
                'backward_compatibility' => 'UU',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => 'TST',
                    'internal_name' => 'Updated Test Country',
                    'backward_compatibility' => 'UU',
                ],
            ]);
    }

    public function test_country_deletion(): void
    {
        $user = User::factory()->create();
        $country = \App\Models\Country::factory()->create([
            'id' => 'TST',
            'internal_name' => 'Test Country',
            'backward_compatibility' => 'TT',
        ]);

        $response = $this->actingAs($user)
            ->deleteJson(route('country.destroy', $country->id));

        $response->assertStatus(204);
        $this->assertDatabaseMissing('countries', ['id' => 'TST']);
    }

    public function test_country_retrieval(): void
    {
        $user = User::factory()->create();
        $country = \App\Models\Country::factory()->create([
            'id' => 'TST',
            'internal_name' => 'Test Country',
            'backward_compatibility' => 'TT',
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('country.show', $country->id));

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => 'TST',
                    'internal_name' => 'Test Country',
                    'backward_compatibility' => 'TT',
                ],
            ]);
    }

    public function test_country_retrieval__not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('country.show', 'nonexistent'));

        $response->assertStatus(404);
    }

    public function test_country_list_retrieval(): void
    {
        $user = User::factory()->create();
        $countries = \App\Models\Country::factory()->count(3)->create();

        $response = $this->actingAs($user)
            ->getJson(route('country.index'));

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonFragment([
                'id' => $countries[0]->id,
                'internal_name' => $countries[0]->internal_name,
                'backward_compatibility' => $countries[0]->backward_compatibility,
            ]);
    }

    public function test_country_list_retrieval__empty(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('country.index'));

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }
}

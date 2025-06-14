<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

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

    public function testCountryCreation(): void
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
}
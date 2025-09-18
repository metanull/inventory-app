<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Countries;

use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_country_can_be_updated(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $country = Country::factory()->create(['internal_name' => 'Old Name']);
        $this->actingAs($user);

        $payload = [
            'internal_name' => 'New Name',
            'backward_compatibility' => $country->backward_compatibility,
        ];
        $response = $this->put(route('countries.update', $country), $payload);
        $response->assertRedirect();
        $this->assertDatabaseHas('countries', [
            'id' => $country->id,
            'internal_name' => 'New Name',
        ]);
    }
}

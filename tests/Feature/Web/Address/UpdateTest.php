<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Address;

use App\Models\Address;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_update_modifies_address_and_redirects(): void
    {
        $country1 = Country::factory()->create();
        $country2 = Country::factory()->create();

        $address = Address::factory()->create([
            'internal_name' => 'Original Name',
            'country_id' => $country1->id,
        ]);

        $payload = [
            'internal_name' => 'Updated Name',
            'country_id' => $country2->id,
        ];

        $response = $this->put(route('addresses.update', $address), $payload);
        $response->assertRedirect();
        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'internal_name' => 'Updated Name',
            'country_id' => $country2->id,
        ]);
    }

    public function test_update_validation_errors(): void
    {
        $address = Address::factory()->create();

        $response = $this->put(route('addresses.update', $address), [
            'internal_name' => '',
        ]);
        $response->assertSessionHasErrors(['internal_name']);
    }
}

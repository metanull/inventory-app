<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Address;

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class StoreTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_store_persists_address_and_redirects(): void
    {
        $country = Country::factory()->create();

        $payload = [
            'internal_name' => 'Test Address',
            'country_id' => $country->id,
        ];

        $response = $this->post(route('addresses.store'), $payload);

        // Debug: check for validation errors
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('addresses', [
            'internal_name' => 'Test Address',
            'country_id' => $country->id,
        ]);
    }

    public function test_store_validation_errors(): void
    {
        $response = $this->post(route('addresses.store'), [
            'internal_name' => '',
        ]);
        $response->assertSessionHasErrors(['internal_name', 'country_id']);
    }

    public function test_store_requires_valid_country(): void
    {
        $response = $this->post(route('addresses.store'), [
            'internal_name' => 'Test',
            'country_id' => 'invalid-uuid',
        ]);
        $response->assertSessionHasErrors(['country_id']);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Partner;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class StoreTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsDataUser();
    }

    public function test_store_persists_partner_and_redirects(): void
    {
        $payload = [
            'internal_name' => 'Test Partner',
            'type' => 'museum',
            'backward_compatibility' => 'LEG-P1',
            'country_id' => null,
            'visible' => true,
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'map_zoom' => 15,
        ];
        $response = $this->post(route('partners.store'), $payload);
        $response->assertRedirect();
        $this->assertDatabaseHas('partners', [
            'internal_name' => 'Test Partner',
            'type' => 'museum',
            'visible' => true,
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ]);
    }

    public function test_store_validation_errors(): void
    {
        $response = $this->post(route('partners.store'), [
            'internal_name' => '',
            'type' => 'invalid',
        ]);
        $response->assertSessionHasErrors(['internal_name', 'type']);
    }

    public function test_store_rejects_invalid_country(): void
    {
        $response = $this->post(route('partners.store'), [
            'internal_name' => 'Partner X',
            'type' => 'museum',
            'country_id' => 'ZZZ', // not seeded / invalid
        ]);
        $response->assertSessionHasErrors(['country_id']);
    }

    public function test_store_rejects_lowercase_country(): void
    {
        $response = $this->post(route('partners.store'), [
            'internal_name' => 'Partner Y',
            'type' => 'institution',
            'country_id' => 'usa', // lowercase triggers uppercase rule failure
        ]);
        $response->assertSessionHasErrors(['country_id']);
    }

    public function test_store_accepts_valid_country(): void
    {
        $country = \App\Models\Country::factory()->create(['id' => 'USA']);
        $response = $this->post(route('partners.store'), [
            'internal_name' => 'Partner Z',
            'type' => 'individual',
            'country_id' => $country->id,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('partners', [
            'internal_name' => 'Partner Z',
            'country_id' => $country->id,
        ]);
    }

    public function test_store_validates_gps_coordinates(): void
    {
        $response = $this->post(route('partners.store'), [
            'internal_name' => 'GPS Test Partner',
            'type' => 'museum',
            'latitude' => 100, // invalid: > 90
            'longitude' => -200, // invalid: < -180
        ]);
        $response->assertSessionHasErrors(['latitude', 'longitude']);
    }

    public function test_store_accepts_valid_gps_coordinates(): void
    {
        $response = $this->post(route('partners.store'), [
            'internal_name' => 'Valid GPS Partner',
            'type' => 'museum',
            'latitude' => 48.8566,
            'longitude' => 2.3522,
            'map_zoom' => 14,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('partners', [
            'internal_name' => 'Valid GPS Partner',
            'latitude' => 48.8566,
            'longitude' => 2.3522,
            'map_zoom' => 14,
        ]);
    }
}

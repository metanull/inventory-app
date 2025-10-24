<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Partner;

use App\Models\Partner;
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

    public function test_update_persists_changes_and_redirects(): void
    {
        $partner = Partner::factory()->create(['internal_name' => 'Original Name']);
        $response = $this->put(route('partners.update', $partner), [
            'internal_name' => 'Updated Name',
            'type' => $partner->type,
            'backward_compatibility' => $partner->backward_compatibility,
            'country_id' => $partner->country_id,
            'visible' => true,
            'latitude' => 51.5074,
            'longitude' => -0.1278,
            'map_zoom' => 12,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('partners', [
            'id' => $partner->id,
            'internal_name' => 'Updated Name',
            'visible' => true,
            'latitude' => 51.5074,
        ]);
    }

    public function test_update_validation_errors(): void
    {
        $partner = Partner::factory()->create();
        $response = $this->put(route('partners.update', $partner), [
            'internal_name' => '',
            'type' => 'invalid',
        ]);
        $response->assertSessionHasErrors(['internal_name', 'type']);
    }

    public function test_update_rejects_invalid_country(): void
    {
        $partner = Partner::factory()->create();
        $response = $this->put(route('partners.update', $partner), [
            'internal_name' => 'Keep Name',
            'type' => $partner->type,
            'backward_compatibility' => $partner->backward_compatibility,
            'country_id' => 'XXX',
        ]);
        $response->assertSessionHasErrors(['country_id']);
    }

    public function test_update_rejects_lowercase_country(): void
    {
        $partner = Partner::factory()->create();
        $response = $this->put(route('partners.update', $partner), [
            'internal_name' => 'Keep Name',
            'type' => $partner->type,
            'backward_compatibility' => $partner->backward_compatibility,
            'country_id' => 'ita',
        ]);
        $response->assertSessionHasErrors(['country_id']);
    }

    public function test_update_accepts_valid_country(): void
    {
        $country = \App\Models\Country::factory()->create(['id' => 'ITA']);
        $partner = Partner::factory()->create();
        $response = $this->put(route('partners.update', $partner), [
            'internal_name' => 'Modified Name',
            'type' => $partner->type,
            'backward_compatibility' => $partner->backward_compatibility,
            'country_id' => $country->id,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('partners', [
            'id' => $partner->id,
            'country_id' => $country->id,
            'internal_name' => 'Modified Name',
        ]);
    }

    public function test_update_gps_coordinates(): void
    {
        $partner = Partner::factory()->create();
        $response = $this->put(route('partners.update', $partner), [
            'internal_name' => $partner->internal_name,
            'type' => $partner->type,
            'backward_compatibility' => $partner->backward_compatibility,
            'country_id' => $partner->country_id,
            'latitude' => 35.6762,
            'longitude' => 139.6503,
            'map_zoom' => 11,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('partners', [
            'id' => $partner->id,
            'latitude' => 35.6762,
            'longitude' => 139.6503,
            'map_zoom' => 11,
        ]);
    }
}

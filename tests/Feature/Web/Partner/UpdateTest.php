<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Partner;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_update_persists_changes_and_redirects(): void
    {
        $partner = Partner::factory()->create(['internal_name' => 'Original Name']);
        $response = $this->put(route('partners.update', $partner), [
            'internal_name' => 'Updated Name',
            'type' => $partner->type,
            'backward_compatibility' => $partner->backward_compatibility,
            'country_id' => $partner->country_id,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('partners', [
            'id' => $partner->id,
            'internal_name' => 'Updated Name',
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
}

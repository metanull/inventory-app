<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Countries;

use App\Models\Country;
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

    public function test_country_can_be_created(): void
    {

        $payload = [
            'id' => 'AAA',
            'internal_name' => 'Alpha Republic',
            'backward_compatibility' => 'AR',
        ];

        $response = $this->post(route('countries.store'), $payload);
        $response->assertRedirect();
        $this->assertDatabaseHas('countries', [
            'id' => 'AAA',
            'internal_name' => 'Alpha Republic',
            'backward_compatibility' => 'AR',
        ]);
    }

    public function test_country_requires_unique_id(): void
    {
        Country::factory()->create(['id' => 'AAA']);

        $payload = [
            'id' => 'AAA',
            'internal_name' => 'Duplicate',
        ];
        $response = $this->post(route('countries.store'), $payload);
        $response->assertSessionHasErrors('id');
    }
}

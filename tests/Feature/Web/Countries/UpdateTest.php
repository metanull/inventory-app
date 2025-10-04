<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Countries;

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    public function test_country_can_be_updated(): void
    {
        $this->actAsRegularUser();
        $country = Country::factory()->create(['internal_name' => 'Old Name']);

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

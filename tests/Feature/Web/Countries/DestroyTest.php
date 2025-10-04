<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Countries;

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class DestroyTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    public function test_country_can_be_deleted(): void
    {
        $this->actAsRegularUser();
        $country = Country::factory()->create();

        $response = $this->delete(route('countries.destroy', $country));
        $response->assertRedirect(route('countries.index'));
        $this->assertDatabaseMissing('countries', ['id' => $country->id]);
    }
}

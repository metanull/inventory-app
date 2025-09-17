<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Countries;

use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    public function test_country_can_be_deleted(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $country = Country::factory()->create();
        $this->actingAs($user);

        $response = $this->delete(route('countries.destroy', $country));
        $response->assertRedirect(route('countries.index'));
        $this->assertDatabaseMissing('countries', ['id' => $country->id]);
    }
}

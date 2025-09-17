<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Countries;

use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_country_can_be_created(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

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
        /** @var User $user */
        $user = User::factory()->create();
        Country::factory()->create(['id' => 'AAA']);
        $this->actingAs($user);

        $payload = [
            'id' => 'AAA',
            'internal_name' => 'Duplicate',
        ];
        $response = $this->post(route('countries.store'), $payload);
        $response->assertSessionHasErrors('id');
    }
}

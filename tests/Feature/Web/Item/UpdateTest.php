<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Item;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class UpdateTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsDataUser();
    }

    public function test_update_persists_changes(): void
    {
        $item = Item::factory()->create(['internal_name' => 'Before']);

        $response = $this->put(route('items.update', $item), [
            'internal_name' => 'After',
            'type' => $item->type,
        ]);

        $response->assertRedirect(route('items.show', $item));
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'internal_name' => 'After',
        ]);
    }

    public function test_update_validation_errors(): void
    {
        $item = Item::factory()->create();

        $response = $this->put(route('items.update', $item), [
            'internal_name' => '',
            'type' => 'invalid',
        ]);

        $response->assertSessionHasErrors(['internal_name', 'type']);
    }

    public function test_update_rejects_invalid_country(): void
    {
        $item = Item::factory()->create();

        $response = $this->put(route('items.update', $item), [
            'internal_name' => $item->internal_name,
            'type' => $item->type,
            'country_id' => 'ZZZ', // invalid country
        ]);

        $response->assertSessionHasErrors(['country_id']);
    }

    public function test_update_rejects_lowercase_country(): void
    {
        $item = Item::factory()->create();

        $response = $this->put(route('items.update', $item), [
            'internal_name' => $item->internal_name,
            'type' => $item->type,
            'country_id' => 'fra', // lowercase triggers uppercase rule failure
        ]);

        $response->assertSessionHasErrors(['country_id']);
    }

    public function test_update_accepts_valid_country(): void
    {
        $item = Item::factory()->create();
        $country = \App\Models\Country::factory()->create(['id' => 'FRA']);

        $response = $this->put(route('items.update', $item), [
            'internal_name' => $item->internal_name,
            'type' => $item->type,
            'country_id' => $country->id,
        ]);

        $response->assertRedirect(route('items.show', $item));
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'country_id' => $country->id,
        ]);
    }

    public function test_update_accepts_null_country(): void
    {
        $item = Item::factory()->create();

        $response = $this->put(route('items.update', $item), [
            'internal_name' => $item->internal_name,
            'type' => $item->type,
            'country_id' => null,
        ]);

        $response->assertRedirect(route('items.show', $item));
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'country_id' => null,
        ]);
    }
}

<?php

namespace Tests\Unit;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_factory(): void
    {
        $item = Item::factory()->create();
        $this->assertInstanceOf(Item::class, $item);
        $this->assertNotEmpty($item->id);
        $this->assertNotEmpty($item->internal_name);
        $this->assertNotEmpty($item->backward_compatibility);
        $this->assertNotEmpty($item->type);
    }

    public function test_factory_object(): void
    {
        $item = Item::factory()->Object()->make();
        $this->assertInstanceOf(Item::class, $item);
        $this->assertEquals('object', $item->type);
    }

    public function test_factory_monument(): void
    {
        $item = Item::factory()->Monument()->make();
        $this->assertInstanceOf(Item::class, $item);
        $this->assertEquals('monument', $item->type);
    }

    public function test_factory_with_partner(): void
    {
        $item = Item::factory()->withPartner()->make();
        $this->assertNotNull($item->partner_id);
        $this->assertInstanceOf(Item::class, $item);
    }

    public function test_factory_with_country(): void
    {
        $item = Item::factory()->withCountry()->make();
        $this->assertNotNull($item->country_id);
        $this->assertInstanceOf(Item::class, $item);
    }

    public function test_factory_with_project(): void
    {
        $item = Item::factory()->withProject()->make();
        $this->assertNotNull($item->project_id);
        $this->assertInstanceOf(Item::class, $item);
    }

    public function test_factory_creates_a_row_in_database(): void
    {
        $item = Item::factory()->create();
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'internal_name' => $item->internal_name,
            'backward_compatibility' => $item->backward_compatibility,
            'partner_id' => null,
            'country_id' => null,
            'project_id' => null,
            'type' => $item->type,
        ]);
    }

    public function test_factory_creates_a_row_in_database_object(): void
    {
        $item = Item::factory()->Object()->create();
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'type' => 'object',
        ]);
    }

    public function test_factory_creates_a_row_in_database_monument(): void
    {
        $item = Item::factory()->Monument()->create();
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'type' => 'monument',
        ]);
    }

    public function test_factory_creates_a_row_in_database_with_partner(): void
    {
        $item = Item::factory()->withPartner()->create();
        $this->assertNotNull($item->partner_id);
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'partner_id' => $item->partner->id,
        ]);
    }

    public function test_factory_creates_a_row_in_database_with_country(): void
    {
        $item = Item::factory()->withCountry()->create();
        $this->assertNotNull($item->country_id);
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'country_id' => $item->country->id,
        ]);
    }

    public function test_factory_creates_a_row_in_database_with_project(): void
    {
        $item = Item::factory()->withProject()->create();
        $this->assertNotNull($item->project_id);
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'project_id' => $item->project->id,
        ]);
    }
}

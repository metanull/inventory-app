<?php

namespace Tests\Unit\Models;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for Item factory states and methods.
 *
 * These tests verify that factory states produce the correct model configurations,
 * not the framework's factory mechanics.
 */
class ItemFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_valid_item(): void
    {
        $item = Item::factory()->create();

        $this->assertInstanceOf(Item::class, $item);
        $this->assertNotEmpty($item->id);
        $this->assertNotEmpty($item->internal_name);
        $this->assertNotEmpty($item->backward_compatibility);
        $this->assertNotEmpty($item->type);
    }

    public function test_factory_states_produce_correct_types(): void
    {
        $object = Item::factory()->Object()->create();
        $monument = Item::factory()->Monument()->create();

        $this->assertEquals('object', $object->type);
        $this->assertEquals('monument', $monument->type);
    }

    public function test_factory_with_partner_creates_partner_relationship(): void
    {
        $item = Item::factory()->withPartner()->create();

        $this->assertNotNull($item->partner_id);
        $this->assertInstanceOf(\App\Models\Partner::class, $item->partner);
    }

    public function test_factory_with_country_creates_country_relationship(): void
    {
        $item = Item::factory()->withCountry()->create();

        $this->assertNotNull($item->country_id);
        $this->assertInstanceOf(\App\Models\Country::class, $item->country);
    }

    public function test_factory_with_project_creates_project_relationship(): void
    {
        $item = Item::factory()->withProject()->create();

        $this->assertNotNull($item->project_id);
        $this->assertInstanceOf(\App\Models\Project::class, $item->project);
    }
}

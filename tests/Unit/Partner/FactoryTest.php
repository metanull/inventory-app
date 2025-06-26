<?php

namespace Tests\Unit\Partner;

use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_factory(): void
    {
        $partner = Partner::factory()->make();
        $this->assertInstanceOf(Partner::class, $partner);
        $this->assertNotNull($partner->internal_name);
        $this->assertNotNull($partner->backward_compatibility);
        $this->assertNull($partner->country_id);
        $this->assertNotNull($partner->type);
    }

    public function test_factory_with_country(): void
    {
        $partner = Partner::factory()->withCountry()->make();
        $this->assertInstanceOf(Partner::class, $partner);
        $this->assertNotNull($partner->country_id);
    }

    public function test_factory_museum(): void
    {
        $partner = Partner::factory()->Museum()->make();
        $this->assertInstanceOf(Partner::class, $partner);
        $this->assertEquals('museum', $partner->type);
    }

    public function test_factory_institution(): void
    {
        $partner = Partner::factory()->Institution()->make();
        $this->assertInstanceOf(Partner::class, $partner);
        $this->assertEquals('institution', $partner->type);
    }

    public function test_factory_individual(): void
    {
        $partner = Partner::factory()->Individual()->make();
        $this->assertInstanceOf(Partner::class, $partner);
        $this->assertEquals('individual', $partner->type);
    }

    public function test_factory_creates_a_row_in_database(): void
    {
        $partner = Partner::factory()->create();
        $this->assertDatabaseHas('partners', [
            'id' => $partner->id,
            'internal_name' => $partner->internal_name,
            'backward_compatibility' => $partner->backward_compatibility,
            'type' => $partner->type,
        ]);
        $this->assertDatabaseCount('partners', 1);
    }

    public function test_factory_creates_a_row_in_database_with_country(): void
    {
        $partner = Partner::factory()->withCountry()->create();
        $this->assertDatabaseHas('partners', [
            'id' => $partner->id,
            'country_id' => $partner->country_id,
        ]);
        $this->assertDatabaseCount('partners', 1);
    }

    public function test_factory_creates_a_row_in_database_museum(): void
    {
        $partner = Partner::factory()->Museum()->create();
        $this->assertDatabaseHas('partners', [
            'id' => $partner->id,
            'type' => 'museum',
        ]);
        $this->assertDatabaseCount('partners', 1);
    }

    public function test_factory_creates_a_row_in_database_institution(): void
    {
        $partner = Partner::factory()->Institution()->create();
        $this->assertDatabaseHas('partners', [
            'id' => $partner->id,
            'type' => 'institution',
        ]);
        $this->assertDatabaseCount('partners', 1);
    }

    public function test_factory_creates_a_row_in_database_individual(): void
    {
        $partner = Partner::factory()->Individual()->create();
        $this->assertDatabaseHas('partners', [
            'id' => $partner->id,
            'type' => 'individual',
        ]);
        $this->assertDatabaseCount('partners', 1);
    }
}

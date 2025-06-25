<?php

namespace Tests\Unit;

use App\Models\Detail;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DetailTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_factory(): void
    {
        $detail = Detail::factory()->make();
        $this->assertInstanceOf(Detail::class, $detail);
        $this->assertNotNull($detail->internal_name);
        $this->assertNotNull($detail->backward_compatibility);
        $this->assertNull($detail->item_id);
    }

    public function test_factory_with_item(): void
    {
        $detail = Detail::factory()->withItem()->make();
        $this->assertInstanceOf(Detail::class, $detail);
        $this->assertNotNull($detail->item_id);
    }
    
    public function test_factory_creates_a_row_in_database(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();
        $this->assertNotNull($detail->item_id);
        $this->assertDatabaseHas('details', [
            'id' => $detail->id,
            'internal_name' => $detail->internal_name,
            'backward_compatibility' => $detail->backward_compatibility,
        ]);
    }

    public function test_factory_creates_a_row_in_database_with_item(): void
    {
        $detail = Detail::factory()->withItem()->create();
        $this->assertNotNull($detail->item_id);
        $this->assertDatabaseHas('details', [
            'id' => $detail->id,
            'item_id' => $detail->item_id,
        ]);
    }

}

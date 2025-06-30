<?php

namespace Tests\Unit\Contextualization;

use App\Models\Context;
use App\Models\Contextualization;
use App\Models\Detail;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_contextualization_factory_creates_valid_model(): void
    {
        $contextualization = Contextualization::factory()->create();

        $this->assertDatabaseHas('contextualizations', [
            'id' => $contextualization->id,
        ]);

        $this->assertNotNull($contextualization->id);
        $this->assertNotNull($contextualization->context_id);
        $this->assertNotNull($contextualization->internal_name);

        // Ensure exactly one of item_id or detail_id is set
        $this->assertTrue(
            ($contextualization->item_id !== null && $contextualization->detail_id === null) ||
            ($contextualization->item_id === null && $contextualization->detail_id !== null)
        );
    }

    public function test_contextualization_factory_creates_for_item(): void
    {
        $item = Item::factory()->create();
        $contextualization = Contextualization::factory()->forItem($item)->create();

        $this->assertEquals($item->id, $contextualization->item_id);
        $this->assertNull($contextualization->detail_id);

        $this->assertDatabaseHas('contextualizations', [
            'id' => $contextualization->id,
            'item_id' => $item->id,
            'detail_id' => null,
        ]);
    }

    public function test_contextualization_factory_creates_for_detail(): void
    {
        $detail = Detail::factory()->create();
        $contextualization = Contextualization::factory()->forDetail($detail)->create();

        $this->assertEquals($detail->id, $contextualization->detail_id);
        $this->assertNull($contextualization->item_id);

        $this->assertDatabaseHas('contextualizations', [
            'id' => $contextualization->id,
            'item_id' => null,
            'detail_id' => $detail->id,
        ]);
    }

    public function test_contextualization_factory_creates_with_default_context(): void
    {
        // First create a default context
        $defaultContext = Context::factory()->create(['is_default' => true]);

        $contextualization = Contextualization::factory()->withDefaultContext()->create();

        $this->assertEquals($defaultContext->id, $contextualization->context_id);

        $this->assertDatabaseHas('contextualizations', [
            'id' => $contextualization->id,
            'context_id' => $defaultContext->id,
        ]);
    }

    public function test_contextualization_factory_generates_unique_internal_names(): void
    {
        $contextualization1 = Contextualization::factory()->create();
        $contextualization2 = Contextualization::factory()->create();

        $this->assertNotEquals($contextualization1->internal_name, $contextualization2->internal_name);
    }

    public function test_contextualization_factory_handles_extra_field(): void
    {
        $extraData = ['key1' => 'value1', 'key2' => 'value2'];

        $contextualization = Contextualization::factory()->create([
            'extra' => $extraData,
        ]);

        $this->assertEquals($extraData, $contextualization->extra);
    }

    public function test_contextualization_factory_handles_backward_compatibility(): void
    {
        $backwardId = $this->faker->uuid();

        $contextualization = Contextualization::factory()->create([
            'backward_compatibility' => $backwardId,
        ]);

        $this->assertEquals($backwardId, $contextualization->backward_compatibility);
    }

    public function test_contextualization_factory_creates_relationships(): void
    {
        $contextualization = Contextualization::factory()->create();

        $this->assertInstanceOf(Context::class, $contextualization->context);

        if ($contextualization->item_id) {
            $this->assertInstanceOf(Item::class, $contextualization->item);
            $this->assertNull($contextualization->detail);
        } else {
            $this->assertInstanceOf(Detail::class, $contextualization->detail);
            $this->assertNull($contextualization->item);
        }
    }

    public function test_contextualization_factory_for_item_without_parameter(): void
    {
        $contextualization = Contextualization::factory()->forItem()->create();

        $this->assertNotNull($contextualization->item_id);
        $this->assertNull($contextualization->detail_id);
        $this->assertInstanceOf(Item::class, $contextualization->item);
    }

    public function test_contextualization_factory_for_detail_without_parameter(): void
    {
        $contextualization = Contextualization::factory()->forDetail()->create();

        $this->assertNotNull($contextualization->detail_id);
        $this->assertNull($contextualization->item_id);
        $this->assertInstanceOf(Detail::class, $contextualization->detail);
    }
}

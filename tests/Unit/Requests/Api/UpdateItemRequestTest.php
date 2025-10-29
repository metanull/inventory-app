<?php

namespace Tests\Unit\Requests\Api;

use App\Http\Requests\Api\UpdateItemRequest;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Tests for API UpdateItemRequest hierarchical validation rules.
 */
class UpdateItemRequestTest extends TestCase
{
    use RefreshDatabase;

    private function assertValidationFails(Item $item, array $data, string $field): void
    {
        $request = new UpdateItemRequest;
        $request->setRouteResolver(function () use ($item) {
            return new class($item)
            {
                public function __construct(private Item $item) {}

                public function parameter($key)
                {
                    return $key === 'item' ? $this->item : null;
                }
            };
        });
        $request->merge($data);

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        try {
            $validator->validate();
            $this->fail('Validation should have failed for field: '.$field);
        } catch (ValidationException $e) {
            $this->assertTrue($validator->errors()->has($field));
        }
    }

    private function assertValidationPasses(Item $item, array $data): void
    {
        $request = new UpdateItemRequest;
        $request->setRouteResolver(function () use ($item) {
            return new class($item)
            {
                public function __construct(private Item $item) {}

                public function parameter($key)
                {
                    return $key === 'item' ? $this->item : null;
                }
            };
        });
        $request->merge($data);

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        $validator->validate();
        $this->assertFalse($validator->errors()->any());
    }

    public function test_object_type_cannot_have_parent(): void
    {
        $item = Item::factory()->create(['type' => 'monument']);
        $parent = Item::factory()->create();

        $this->assertValidationFails($item, [
            'type' => 'object',
            'parent_id' => $parent->id,
        ], 'parent_id');
    }

    public function test_monument_type_cannot_have_parent(): void
    {
        $item = Item::factory()->create(['type' => 'object']);
        $parent = Item::factory()->create();

        $this->assertValidationFails($item, [
            'type' => 'monument',
            'parent_id' => $parent->id,
        ], 'parent_id');
    }

    public function test_cannot_set_item_as_its_own_parent(): void
    {
        $item = Item::factory()->create(['type' => 'detail']);

        $this->assertValidationFails($item, [
            'parent_id' => $item->id,
        ], 'parent_id');
    }

    public function test_detail_type_must_have_valid_parent(): void
    {
        $item = Item::factory()->create(['type' => 'object']);
        $invalidParent = Item::factory()->create(['type' => 'picture']);

        $this->assertValidationFails($item, [
            'type' => 'detail',
            'parent_id' => $invalidParent->id,
        ], 'parent_id');
    }

    public function test_validates_only_when_type_or_parent_changes(): void
    {
        $objectParent = Item::factory()->create(['type' => 'object']);
        $item = Item::factory()->create(['type' => 'detail', 'parent_id' => $objectParent->id]);

        // Only updating name, should not trigger hierarchical validation
        $this->assertValidationPasses($item, [
            'internal_name' => 'New Name',
        ]);
    }
}

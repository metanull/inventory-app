<?php

namespace Tests\Unit\Requests\Api;

use App\Enums\ItemType;
use App\Http\Requests\Api\UpdateItemRequest;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Tests for API UpdateItemRequest validation rules.
 *
 * Tests only custom business logic - does not test framework validation.
 * Note: There are NO constraints on parent/child relationships for items.
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

    public function test_cannot_set_item_as_its_own_parent(): void
    {
        $item = Item::factory()->create();

        $this->assertValidationFails($item, [
            'parent_id' => $item->id,
        ], 'parent_id');
    }

    public function test_any_type_can_have_parent(): void
    {
        $parent = Item::factory()->create();

        foreach (ItemType::cases() as $type) {
            $item = Item::factory()->create();
            
            $this->assertValidationPasses($item, [
                'type' => $type->value,
                'parent_id' => $parent->id,
            ]);
        }
    }

    public function test_can_update_without_changing_parent(): void
    {
        $parent = Item::factory()->create();
        $item = Item::factory()->create(['parent_id' => $parent->id]);

        $this->assertValidationPasses($item, [
            'internal_name' => 'New Name',
        ]);
    }
}

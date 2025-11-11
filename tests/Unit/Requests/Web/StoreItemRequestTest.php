<?php

namespace Tests\Unit\Requests\Web;

use App\Http\Requests\Web\StoreItemRequest;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Tests for Web StoreItemRequest validation rules.
 *
 * Tests only custom business logic - does not test framework validation.
 * Note: There are NO constraints on parent/child relationships for items.
 */
class StoreItemRequestTest extends TestCase
{
    use RefreshDatabase;

    private function assertValidationPasses(array $data): void
    {
        $request = new StoreItemRequest;
        $request->merge($data);

        $validator = Validator::make($request->all(), $request->rules());

        $validator->validate();
        $this->assertFalse($validator->errors()->any());
    }

    public function test_accepts_all_item_types(): void
    {
        foreach (\App\Enums\ItemType::cases() as $type) {
            $this->assertValidationPasses([
                'internal_name' => 'Test '.$type->value,
                'type' => $type->value,
            ]);
        }
    }

    public function test_any_type_can_have_parent(): void
    {
        $parent = Item::factory()->create();

        foreach (\App\Enums\ItemType::cases() as $type) {
            $this->assertValidationPasses([
                'internal_name' => 'Test '.$type->value,
                'type' => $type->value,
                'parent_id' => $parent->id,
            ]);
        }
    }

    public function test_any_type_can_be_without_parent(): void
    {
        foreach (\App\Enums\ItemType::cases() as $type) {
            $this->assertValidationPasses([
                'internal_name' => 'Test '.$type->value,
                'type' => $type->value,
                'parent_id' => null,
            ]);
        }
    }
}

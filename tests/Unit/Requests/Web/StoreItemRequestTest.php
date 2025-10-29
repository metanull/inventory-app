<?php

namespace Tests\Unit\Requests\Web;

use App\Http\Requests\Web\StoreItemRequest;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Tests for StoreItemRequest hierarchical validation rules.
 *
 * Tests the custom business logic that enforces proper
 * parent-child relationships between item types.
 */
class StoreItemRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper to validate request data and expect failure.
     */
    private function assertValidationFails(array $data, string $field): void
    {
        $request = new StoreItemRequest;
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

    /**
     * Helper to validate request data and expect success.
     */
    private function assertValidationPasses(array $data): void
    {
        $request = new StoreItemRequest;
        $request->merge($data);

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        $validator->validate();
        $this->assertFalse($validator->errors()->any());
    }

    public function test_object_type_cannot_have_parent(): void
    {
        $parent = Item::factory()->create();

        $this->assertValidationFails([
            'internal_name' => 'Test Object',
            'type' => 'object',
            'parent_id' => $parent->id,
        ], 'parent_id');
    }

    public function test_monument_type_cannot_have_parent(): void
    {
        $parent = Item::factory()->create();

        $this->assertValidationFails([
            'internal_name' => 'Test Monument',
            'type' => 'monument',
            'parent_id' => $parent->id,
        ], 'parent_id');
    }

    public function test_detail_type_must_have_parent(): void
    {
        $this->assertValidationFails([
            'internal_name' => 'Test Detail',
            'type' => 'detail',
            'parent_id' => null,
        ], 'parent_id');
    }

    public function test_detail_type_parent_must_be_object_or_monument(): void
    {
        $invalidParent = Item::factory()->create(['type' => 'picture']);

        $this->assertValidationFails([
            'internal_name' => 'Test Detail',
            'type' => 'detail',
            'parent_id' => $invalidParent->id,
        ], 'parent_id');
    }

    public function test_detail_accepts_object_parent(): void
    {
        $objectParent = Item::factory()->create(['type' => 'object']);

        $this->assertValidationPasses([
            'internal_name' => 'Test Detail',
            'type' => 'detail',
            'parent_id' => $objectParent->id,
        ]);
    }

    public function test_detail_accepts_monument_parent(): void
    {
        $monumentParent = Item::factory()->create(['type' => 'monument']);

        $this->assertValidationPasses([
            'internal_name' => 'Test Detail',
            'type' => 'detail',
            'parent_id' => $monumentParent->id,
        ]);
    }

    public function test_picture_accepts_object_parent(): void
    {
        $objectParent = Item::factory()->create(['type' => 'object']);

        $this->assertValidationPasses([
            'internal_name' => 'Test Picture',
            'type' => 'picture',
            'parent_id' => $objectParent->id,
        ]);
    }

    public function test_picture_rejects_picture_as_parent(): void
    {
        $pictureParent = Item::factory()->create(['type' => 'picture']);

        $this->assertValidationFails([
            'internal_name' => 'Test Picture',
            'type' => 'picture',
            'parent_id' => $pictureParent->id,
        ], 'parent_id');
    }
}

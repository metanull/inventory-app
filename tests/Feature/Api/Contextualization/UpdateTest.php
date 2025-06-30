<?php

namespace Tests\Feature\Api\Contextualization;

use App\Models\Context;
use App\Models\Contextualization;
use App\Models\Detail;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_authenticated_user_can_update_contextualization(): void
    {
        $contextualization = Contextualization::factory()->create();
        $newContext = Context::factory()->create();

        $updateData = [
            'context_id' => $newContext->id,
            'internal_name' => 'updated-name',
        ];

        $response = $this->putJson(route('contextualization.update', $contextualization), $updateData);

        $response->assertOk();
        $response->assertJsonPath('data.context_id', $newContext->id);
        $response->assertJsonPath('data.internal_name', 'updated-name');

        $this->assertDatabaseHas('contextualizations', [
            'id' => $contextualization->id,
            'context_id' => $newContext->id,
            'internal_name' => 'updated-name',
        ]);
    }

    public function test_update_can_change_from_item_to_detail(): void
    {
        $contextualization = Contextualization::factory()->forItem()->create();
        $detail = Detail::factory()->create();

        $updateData = [
            'item_id' => null,
            'detail_id' => $detail->id,
        ];

        $response = $this->putJson(route('contextualization.update', $contextualization), $updateData);

        $response->assertOk();
        $response->assertJsonPath('data.item_id', null);
        $response->assertJsonPath('data.detail_id', $detail->id);

        $this->assertDatabaseHas('contextualizations', [
            'id' => $contextualization->id,
            'item_id' => null,
            'detail_id' => $detail->id,
        ]);
    }

    public function test_update_can_change_from_detail_to_item(): void
    {
        $contextualization = Contextualization::factory()->forDetail()->create();
        $item = Item::factory()->create();

        $updateData = [
            'item_id' => $item->id,
            'detail_id' => null,
        ];

        $response = $this->putJson(route('contextualization.update', $contextualization), $updateData);

        $response->assertOk();
        $response->assertJsonPath('data.item_id', $item->id);
        $response->assertJsonPath('data.detail_id', null);

        $this->assertDatabaseHas('contextualizations', [
            'id' => $contextualization->id,
            'item_id' => $item->id,
            'detail_id' => null,
        ]);
    }

    public function test_update_can_modify_extra_data(): void
    {
        $contextualization = Contextualization::factory()->create([
            'extra' => ['old_key' => 'old_value'],
        ]);

        $newExtraData = ['new_key' => 'new_value', 'another_key' => 'another_value'];
        $updateData = [
            'extra' => $newExtraData,
        ];

        $response = $this->putJson(route('contextualization.update', $contextualization), $updateData);

        $response->assertOk();
        $response->assertJsonPath('data.extra', $newExtraData);
    }

    public function test_update_validates_unique_internal_name(): void
    {
        $existingContextualization = Contextualization::factory()->create();
        $contextualization = Contextualization::factory()->create();

        $updateData = [
            'internal_name' => $existingContextualization->internal_name,
        ];

        $response = $this->putJson(route('contextualization.update', $contextualization), $updateData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_update_allows_same_internal_name(): void
    {
        $contextualization = Contextualization::factory()->create();

        $updateData = [
            'internal_name' => $contextualization->internal_name,
        ];

        $response = $this->putJson(route('contextualization.update', $contextualization), $updateData);

        $response->assertOk();
        $response->assertJsonPath('data.internal_name', $contextualization->internal_name);
    }

    public function test_update_rejects_both_item_and_detail(): void
    {
        $contextualization = Contextualization::factory()->forItem()->create();
        $item = Item::factory()->create();
        $detail = Detail::factory()->create();

        $updateData = [
            'item_id' => $item->id,
            'detail_id' => $detail->id,
        ];

        $response = $this->putJson(route('contextualization.update', $contextualization), $updateData);

        $response->assertUnprocessable();
        $response->assertJsonPath('message', 'Exactly one of item_id or detail_id must be provided.');
    }

    public function test_update_rejects_neither_item_nor_detail(): void
    {
        $contextualization = Contextualization::factory()->forItem()->create();

        $updateData = [
            'item_id' => null,
            'detail_id' => null,
        ];

        $response = $this->putJson(route('contextualization.update', $contextualization), $updateData);

        $response->assertUnprocessable();
        $response->assertJsonPath('message', 'Exactly one of item_id or detail_id must be provided.');
    }

    public function test_update_validates_context_exists(): void
    {
        $contextualization = Contextualization::factory()->create();

        $updateData = [
            'context_id' => '99999999-9999-9999-9999-999999999999',
        ];

        $response = $this->putJson(route('contextualization.update', $contextualization), $updateData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['context_id']);
    }

    public function test_update_validates_item_exists(): void
    {
        $contextualization = Contextualization::factory()->forDetail()->create();

        $updateData = [
            'item_id' => '99999999-9999-9999-9999-999999999999',
            'detail_id' => null,
        ];

        $response = $this->putJson(route('contextualization.update', $contextualization), $updateData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['item_id']);
    }

    public function test_update_validates_detail_exists(): void
    {
        $contextualization = Contextualization::factory()->forItem()->create();

        $updateData = [
            'item_id' => null,
            'detail_id' => '99999999-9999-9999-9999-999999999999',
        ];

        $response = $this->putJson(route('contextualization.update', $contextualization), $updateData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['detail_id']);
    }

    public function test_update_partial_data(): void
    {
        $contextualization = Contextualization::factory()->create();
        $originalInternalName = $contextualization->internal_name;

        $updateData = [
            'backward_compatibility' => 'updated-backward-id',
        ];

        $response = $this->putJson(route('contextualization.update', $contextualization), $updateData);

        $response->assertOk();
        $response->assertJsonPath('data.backward_compatibility', 'updated-backward-id');
        $response->assertJsonPath('data.internal_name', $originalInternalName);
    }

    public function test_update_returns_not_found_for_nonexistent_contextualization(): void
    {
        $updateData = [
            'internal_name' => 'new-name',
        ];

        $response = $this->putJson(route('contextualization.update', '99999999-9999-9999-9999-999999999999'), $updateData);

        $response->assertNotFound();
    }
}

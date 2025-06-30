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

class StoreTest extends TestCase
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

    public function test_authenticated_user_can_create_contextualization_for_item(): void
    {
        $context = Context::factory()->create();
        $item = Item::factory()->create();

        $contextualizeData = [
            'context_id' => $context->id,
            'item_id' => $item->id,
            'detail_id' => null,
            'internal_name' => 'test-contextualization',
        ];

        $response = $this->postJson(route('contextualization.store'), $contextualizeData);

        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'context_id',
                'item_id',
                'detail_id',
                'extra',
                'internal_name',
                'backward_compatibility',
                'created_at',
                'updated_at',
                'context',
                'item',
                'detail',
            ],
        ]);
        $response->assertJsonPath('data.context_id', $context->id);
        $response->assertJsonPath('data.item_id', $item->id);
        $response->assertJsonPath('data.detail_id', null);

        $this->assertDatabaseHas('contextualizations', [
            'context_id' => $context->id,
            'item_id' => $item->id,
            'detail_id' => null,
            'internal_name' => 'test-contextualization',
        ]);
    }

    public function test_authenticated_user_can_create_contextualization_for_detail(): void
    {
        $context = Context::factory()->create();
        $detail = Detail::factory()->create();

        $contextualizeData = [
            'context_id' => $context->id,
            'item_id' => null,
            'detail_id' => $detail->id,
            'internal_name' => 'test-contextualization-detail',
        ];

        $response = $this->postJson(route('contextualization.store'), $contextualizeData);

        $response->assertCreated();
        $response->assertJsonPath('data.context_id', $context->id);
        $response->assertJsonPath('data.item_id', null);
        $response->assertJsonPath('data.detail_id', $detail->id);

        $this->assertDatabaseHas('contextualizations', [
            'context_id' => $context->id,
            'item_id' => null,
            'detail_id' => $detail->id,
            'internal_name' => 'test-contextualization-detail',
        ]);
    }

    public function test_create_with_extra_data(): void
    {
        $context = Context::factory()->create();
        $item = Item::factory()->create();
        $extraData = ['key1' => 'value1', 'key2' => 'value2'];

        $contextualizeData = [
            'context_id' => $context->id,
            'item_id' => $item->id,
            'detail_id' => null,
            'internal_name' => 'test-with-extra',
            'extra' => $extraData,
        ];

        $response = $this->postJson(route('contextualization.store'), $contextualizeData);

        $response->assertCreated();
        $response->assertJsonPath('data.extra', $extraData);

        $this->assertDatabaseHas('contextualizations', [
            'internal_name' => 'test-with-extra',
        ]);
    }

    public function test_create_requires_context_id(): void
    {
        $item = Item::factory()->create();

        $contextualizeData = [
            'item_id' => $item->id,
            'detail_id' => null,
            'internal_name' => 'test-no-context',
        ];

        $response = $this->postJson(route('contextualization.store'), $contextualizeData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['context_id']);
    }

    public function test_create_requires_internal_name(): void
    {
        $context = Context::factory()->create();
        $item = Item::factory()->create();

        $contextualizeData = [
            'context_id' => $context->id,
            'item_id' => $item->id,
            'detail_id' => null,
        ];

        $response = $this->postJson(route('contextualization.store'), $contextualizeData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_create_requires_unique_internal_name(): void
    {
        $existingContextualization = Contextualization::factory()->create();
        $context = Context::factory()->create();
        $item = Item::factory()->create();

        $contextualizeData = [
            'context_id' => $context->id,
            'item_id' => $item->id,
            'detail_id' => null,
            'internal_name' => $existingContextualization->internal_name,
        ];

        $response = $this->postJson(route('contextualization.store'), $contextualizeData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_create_rejects_both_item_and_detail(): void
    {
        $context = Context::factory()->create();
        $item = Item::factory()->create();
        $detail = Detail::factory()->create();

        $contextualizeData = [
            'context_id' => $context->id,
            'item_id' => $item->id,
            'detail_id' => $detail->id,
            'internal_name' => 'test-both',
        ];

        $response = $this->postJson(route('contextualization.store'), $contextualizeData);

        $response->assertUnprocessable();
        $response->assertJsonPath('message', 'Exactly one of item_id or detail_id must be provided.');
    }

    public function test_create_rejects_neither_item_nor_detail(): void
    {
        $context = Context::factory()->create();

        $contextualizeData = [
            'context_id' => $context->id,
            'item_id' => null,
            'detail_id' => null,
            'internal_name' => 'test-neither',
        ];

        $response = $this->postJson(route('contextualization.store'), $contextualizeData);

        $response->assertUnprocessable();
        $response->assertJsonPath('message', 'Exactly one of item_id or detail_id must be provided.');
    }

    public function test_create_validates_context_exists(): void
    {
        $item = Item::factory()->create();

        $contextualizeData = [
            'context_id' => '99999999-9999-9999-9999-999999999999',
            'item_id' => $item->id,
            'detail_id' => null,
            'internal_name' => 'test-invalid-context',
        ];

        $response = $this->postJson(route('contextualization.store'), $contextualizeData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['context_id']);
    }

    public function test_create_validates_item_exists(): void
    {
        $context = Context::factory()->create();

        $contextualizeData = [
            'context_id' => $context->id,
            'item_id' => '99999999-9999-9999-9999-999999999999',
            'detail_id' => null,
            'internal_name' => 'test-invalid-item',
        ];

        $response = $this->postJson(route('contextualization.store'), $contextualizeData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['item_id']);
    }

    public function test_create_validates_detail_exists(): void
    {
        $context = Context::factory()->create();

        $contextualizeData = [
            'context_id' => $context->id,
            'item_id' => null,
            'detail_id' => '99999999-9999-9999-9999-999999999999',
            'internal_name' => 'test-invalid-detail',
        ];

        $response = $this->postJson(route('contextualization.store'), $contextualizeData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['detail_id']);
    }

    public function test_store_with_default_context(): void
    {
        Context::factory()->create(['is_default' => true]);
        $item = Item::factory()->create();

        $contextualizeData = [
            'item_id' => $item->id,
            'detail_id' => null,
            'internal_name' => 'test-default-context',
        ];

        $response = $this->postJson(route('contextualization.storeWithDefaultContext'), $contextualizeData);

        $response->assertCreated();
        $response->assertJsonPath('data.item_id', $item->id);
        $response->assertJsonPath('data.detail_id', null);

        // Verify it used the default context
        $defaultContext = Context::default()->first();
        $response->assertJsonPath('data.context_id', $defaultContext->id);
    }
}

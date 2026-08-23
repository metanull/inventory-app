<?php

namespace Tests\Api\Resources;

use App\Models\Collection;
use App\Models\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Api\Traits\AuthenticatesApiRequests;
use Tests\Api\Traits\TestsApiCrud;
use Tests\TestCase;

class CollectionTest extends TestCase
{
    use AuthenticatesApiRequests;
    use RefreshDatabase;
    use TestsApiCrud;

    protected function getResourceName(): string
    {
        return 'collection';
    }

    protected function getModelClass(): string
    {
        return Collection::class;
    }

    public function test_can_create_collection_with_purpose(): void
    {
        $data = Collection::factory()->make()->toArray();
        $data['purpose'] = Collection::PURPOSE_EXHIBITIONS_ROOT;

        $response = $this->postJson(route('collection.store'), $data);

        $response->assertCreated()
            ->assertJsonPath('data.purpose', Collection::PURPOSE_EXHIBITIONS_ROOT);

        $this->assertDatabaseHas('collections', [
            'internal_name' => $data['internal_name'],
            'purpose' => Collection::PURPOSE_EXHIBITIONS_ROOT,
        ]);
    }

    public function test_cannot_create_collection_with_unknown_purpose(): void
    {
        $data = Collection::factory()->make()->toArray();
        $data['purpose'] = 'not-a-known-purpose';

        $response = $this->postJson(route('collection.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['purpose']);
    }

    public function test_root_purpose_is_unique_per_context(): void
    {
        $context = Context::factory()->create();
        Collection::factory()
            ->withContext($context->id)
            ->withPurpose(Collection::PURPOSE_EXHIBITIONS_ROOT)
            ->create();

        $data = Collection::factory()->make(['context_id' => $context->id])->toArray();
        $data['purpose'] = Collection::PURPOSE_EXHIBITIONS_ROOT;

        $response = $this->postJson(route('collection.store'), $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['purpose']);
    }

    public function test_same_root_purpose_is_allowed_in_different_contexts(): void
    {
        Collection::factory()
            ->withPurpose(Collection::PURPOSE_EXHIBITIONS_ROOT)
            ->create();

        $data = Collection::factory()->make()->toArray();
        $data['purpose'] = Collection::PURPOSE_EXHIBITIONS_ROOT;

        $response = $this->postJson(route('collection.store'), $data);

        $response->assertCreated()
            ->assertJsonPath('data.purpose', Collection::PURPOSE_EXHIBITIONS_ROOT);
    }

    public function test_national_context_purpose_may_repeat_within_a_context(): void
    {
        $context = Context::factory()->create();
        Collection::factory()
            ->withContext($context->id)
            ->withPurpose(Collection::PURPOSE_NATIONAL_CONTEXT)
            ->create();

        $data = Collection::factory()->make(['context_id' => $context->id])->toArray();
        $data['purpose'] = Collection::PURPOSE_NATIONAL_CONTEXT;

        $response = $this->postJson(route('collection.store'), $data);

        $response->assertCreated()
            ->assertJsonPath('data.purpose', Collection::PURPOSE_NATIONAL_CONTEXT);
    }

    public function test_can_update_collection_purpose_and_clear_it(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->putJson(route('collection.update', $collection), [
            'purpose' => Collection::PURPOSE_HISTORICAL_PROFILES_ROOT,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.purpose', Collection::PURPOSE_HISTORICAL_PROFILES_ROOT);

        $response = $this->putJson(route('collection.update', $collection), [
            'purpose' => null,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.purpose', null);

        $this->assertDatabaseHas('collections', [
            'id' => $collection->id,
            'purpose' => null,
        ]);
    }

    public function test_updating_a_root_purpose_collection_does_not_collide_with_itself(): void
    {
        $collection = Collection::factory()
            ->withPurpose(Collection::PURPOSE_EXHIBITIONS_ROOT)
            ->create();

        $response = $this->putJson(route('collection.update', $collection), [
            'purpose' => Collection::PURPOSE_EXHIBITIONS_ROOT,
            'display_order' => 5,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.purpose', Collection::PURPOSE_EXHIBITIONS_ROOT);
    }
}

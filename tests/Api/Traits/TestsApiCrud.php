<?php

namespace Tests\Api\Traits;

/**
 * Standard CRUD Operations Test Trait
 *
 * Provides standard tests for resources that support basic CRUD operations.
 * Tests the happy path for index, show, store, update, and destroy operations.
 *
 * Usage:
 * ```php
 * use TestsApiCrud;
 *
 * protected string $resourceName = 'item';        // Route name prefix
 * protected string $modelClass = Item::class;     // Model class
 * protected array $factoryData = ['key' => 'value']; // Optional factory overrides
 * ```
 */
trait TestsApiCrud
{
    abstract protected function getResourceName(): string;

    abstract protected function getModelClass(): string;

    protected function getFactoryData(): array
    {
        return [];
    }

    public function test_can_list_resources(): void
    {
        $modelClass = $this->getModelClass();
        $resources = $modelClass::factory()->count(3)->create($this->getFactoryData());

        $response = $this->getJson(route($this->getResourceName().'.index'));

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_index_returns_empty_when_no_resources(): void
    {
        $response = $this->getJson(route($this->getResourceName().'.index'));

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_can_show_single_resource(): void
    {
        $modelClass = $this->getModelClass();
        $resource = $modelClass::factory()->create($this->getFactoryData());

        $response = $this->getJson(route($this->getResourceName().'.show', $resource));

        $response->assertOk()
            ->assertJsonPath('data.id', $resource->id);
    }

    public function test_show_returns_404_for_nonexistent_resource(): void
    {
        $response = $this->getJson(route($this->getResourceName().'.show', 'nonexistent-id'));

        $response->assertNotFound();
    }

    public function test_can_create_resource(): void
    {
        $modelClass = $this->getModelClass();
        $data = $modelClass::factory()->make($this->getFactoryData())->toArray();

        // Remove id, timestamps, and system-managed fields from request data
        $data = array_diff_key($data, array_flip(['id', 'created_at', 'updated_at', 'deleted_at', 'is_default']));

        $response = $this->postJson(route($this->getResourceName().'.store'), $data);
        $response->assertCreated()
            ->assertJsonStructure(['data' => ['id']]);

        $this->assertDatabaseHas($modelClass::make()->getTable(),
            array_intersect_key($data, array_flip($modelClass::make()->getFillable()))
        );
    }

    public function test_can_update_resource(): void
    {
        $modelClass = $this->getModelClass();
        $resource = $modelClass::factory()->create($this->getFactoryData());
        $updateData = $modelClass::factory()->make($this->getFactoryData())->toArray();

        // Remove id, timestamps, and system-managed fields from request data
        $updateData = array_diff_key($updateData, array_flip(['id', 'created_at', 'updated_at', 'deleted_at', 'is_default']));

        $response = $this->putJson(route($this->getResourceName().'.update', $resource), $updateData);
        $response->assertOk()
            ->assertJsonPath('data.id', $resource->id);

        $this->assertDatabaseHas($modelClass::make()->getTable(),
            ['id' => $resource->id] + array_intersect_key($updateData, array_flip($modelClass::make()->getFillable()))
        );
    }

    public function test_update_returns_404_for_nonexistent_resource(): void
    {
        $modelClass = $this->getModelClass();
        $updateData = $modelClass::factory()->make($this->getFactoryData())->toArray();

        $response = $this->putJson(route($this->getResourceName().'.update', 'nonexistent-id'), $updateData);

        $response->assertNotFound();
    }

    public function test_can_delete_resource(): void
    {
        $modelClass = $this->getModelClass();
        $resource = $modelClass::factory()->create($this->getFactoryData());

        $response = $this->deleteJson(route($this->getResourceName().'.destroy', $resource));

        $response->assertNoContent();
        $this->assertDatabaseMissing($modelClass::make()->getTable(), ['id' => $resource->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_resource(): void
    {
        $response = $this->deleteJson(route($this->getResourceName().'.destroy', 'nonexistent-id'));

        $response->assertNotFound();
    }
}

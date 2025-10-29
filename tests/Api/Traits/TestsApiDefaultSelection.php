<?php

namespace Tests\Api\Traits;

/**
 * Default Selection Test Trait
 *
 * Provides tests for resources that support a "default" flag (like Language, Context).
 * Tests setting/unsetting default and ensuring only one default exists.
 */
trait TestsApiDefaultSelection
{
    abstract protected function getResourceName(): string;

    abstract protected function getModelClass(): string;

    public function test_can_set_resource_as_default(): void
    {
        $modelClass = $this->getModelClass();
        $resource = $modelClass::factory()->create();

        $response = $this->patchJson(route($this->getResourceName().'.setDefault', $resource), [
            'is_default' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.is_default', true);

        $this->assertDatabaseHas($resource->getTable(), [
            'id' => $resource->id,
            'is_default' => true,
        ]);
    }

    public function test_can_unset_default_resource(): void
    {
        $modelClass = $this->getModelClass();
        $resource = $modelClass::factory()->withIsDefault()->create();

        $response = $this->patchJson(route($this->getResourceName().'.setDefault', $resource), [
            'is_default' => false,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.is_default', false);
    }

    public function test_setting_default_clears_previous_default(): void
    {
        $modelClass = $this->getModelClass();
        $existingDefault = $modelClass::factory()->withIsDefault()->create();
        $newDefault = $modelClass::factory()->create();

        $response = $this->patchJson(route($this->getResourceName().'.setDefault', $newDefault), [
            'is_default' => true,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas($newDefault->getTable(), [
            'id' => $newDefault->id,
            'is_default' => true,
        ]);

        $this->assertDatabaseHas($existingDefault->getTable(), [
            'id' => $existingDefault->id,
            'is_default' => false,
        ]);
    }

    public function test_can_get_default_resource(): void
    {
        $modelClass = $this->getModelClass();
        $default = $modelClass::factory()->withIsDefault()->create();

        $response = $this->getJson(route($this->getResourceName().'.getDefault'));

        $response->assertOk()
            ->assertJsonPath('data.id', $default->id)
            ->assertJsonPath('data.is_default', true);
    }

    public function test_get_default_returns_404_when_none_exists(): void
    {
        $modelClass = $this->getModelClass();
        $modelClass::factory()->create();

        $response = $this->getJson(route($this->getResourceName().'.getDefault'));

        $response->assertNotFound();
    }

    public function test_can_clear_default(): void
    {
        $modelClass = $this->getModelClass();
        $modelClass::factory()->withIsDefault()->create();

        $response = $this->deleteJson(route($this->getResourceName().'.clearDefault'));

        $response->assertOk();

        $this->assertDatabaseMissing($modelClass::make()->getTable(), [
            'is_default' => true,
        ]);
    }
}

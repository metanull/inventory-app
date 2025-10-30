<?php

namespace Tests\Web\Traits;

/**
 * Trait for testing nested Web CRUD operations (nested under parent resource)
 * Example: /items/{item}/links/* routes
 * Provides reusable test methods for index, show, create, edit, store, update, destroy
 */
trait TestsWebNestedCrud
{
    abstract protected function getParentModelClass();

    abstract protected function getModelClass(): string;

    abstract protected function getRouteName(): string;

    abstract protected function getFormData(): array;

    abstract protected function getParentRouteParam();

    /**
     * Get the route parameters (parent + model)
     */
    protected function getRouteParams($model = null): array
    {
        if ($model === null) {
            return [$this->getParentRouteParam()];
        }

        return [$this->getParentRouteParam(), $model];
    }

    public function test_index_page_displays(): void
    {
        $parent = $this->getParentModelClass()::factory()->create();

        $response = $this->get(route($this->getRouteName().'.index', $parent));

        $response->assertOk();
        $response->assertViewIs($this->getIndexView());
    }

    public function test_create_page_displays(): void
    {
        $parent = $this->getParentModelClass()::factory()->create();

        $response = $this->get(route($this->getRouteName().'.create', $parent));

        $response->assertOk();
        $response->assertViewIs($this->getCreateView());
    }

    public function test_show_page_displays(): void
    {
        $parent = $this->getParentModelClass()::factory()->create();
        $modelClass = $this->getModelClass();
        $model = $modelClass::factory()->create(['source_id' => $parent->id] ?? []);

        $response = $this->get(route($this->getRouteName().'.show', [$parent, $model]));

        $response->assertOk();
        $response->assertViewIs($this->getShowView());
    }

    public function test_edit_page_displays(): void
    {
        $parent = $this->getParentModelClass()::factory()->create();
        $modelClass = $this->getModelClass();
        $model = $modelClass::factory()->create(['source_id' => $parent->id] ?? []);

        $response = $this->get(route($this->getRouteName().'.edit', [$parent, $model]));

        $response->assertOk();
        $response->assertViewIs($this->getEditView());
    }

    public function test_store_creates_and_redirects(): void
    {
        $parent = $this->getParentModelClass()::factory()->create();
        $data = $this->getFormData();

        $response = $this->post(route($this->getRouteName().'.store', $parent), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas($this->getTableName(), $this->getDatabaseAssertions($data));
    }

    public function test_update_modifies_and_redirects(): void
    {
        $parent = $this->getParentModelClass()::factory()->create();
        $modelClass = $this->getModelClass();
        $model = $modelClass::factory()->create(['source_id' => $parent->id] ?? []);
        $data = $this->getFormData();

        $response = $this->put(route($this->getRouteName().'.update', [$parent, $model]), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas($this->getTableName(), array_merge(['id' => $model->id], $this->getDatabaseAssertions($data)));
    }

    public function test_destroy_deletes_and_redirects(): void
    {
        $parent = $this->getParentModelClass()::factory()->create();
        $modelClass = $this->getModelClass();
        $model = $modelClass::factory()->create(['source_id' => $parent->id] ?? []);

        $response = $this->delete(route($this->getRouteName().'.destroy', [$parent, $model]));

        $response->assertRedirect();
        $this->assertDatabaseMissing($this->getTableName(), ['id' => $model->id]);
    }

    protected function getTableName(): string
    {
        $modelClass = $this->getModelClass();

        return (new $modelClass)->getTable();
    }

    protected function getDatabaseAssertions(array $data): array
    {
        return array_diff_key($data, array_flip(['id', '_token', '_method']));
    }

    protected function getIndexView(): string
    {
        return $this->getRouteName().'.index';
    }

    protected function getShowView(): string
    {
        return $this->getRouteName().'.show';
    }

    protected function getCreateView(): string
    {
        return $this->getRouteName().'.create';
    }

    protected function getEditView(): string
    {
        return $this->getRouteName().'.edit';
    }
}

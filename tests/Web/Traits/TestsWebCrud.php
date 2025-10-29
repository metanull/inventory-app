<?php

namespace Tests\Web\Traits;

use App\Enums\Permission;

/**
 * Trait for testing standard Web CRUD operations (Blade/Livewire)
 * Provides reusable test methods for index, show, create, edit, store, update, destroy
 * Includes authentication and permission checks
 */
trait TestsWebCrud
{
    abstract protected function getRouteName(): string;

    abstract protected function getModelClass(): string;

    abstract protected function getFormData(): array;

    // ========== Unauthenticated Access Tests ==========

    public function test_index_requires_authentication(): void
    {
        auth()->logout();

        $response = $this->get(route($this->getRouteName().'.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_show_requires_authentication(): void
    {
        $modelClass = $this->getModelClass();
        $model = $modelClass::factory()->create();

        auth()->logout();

        $response = $this->get(route($this->getRouteName().'.show', $model));

        $response->assertRedirect(route('login'));
    }

    public function test_create_requires_authentication(): void
    {
        auth()->logout();

        $response = $this->get(route($this->getRouteName().'.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_edit_requires_authentication(): void
    {
        $modelClass = $this->getModelClass();
        $model = $modelClass::factory()->create();

        auth()->logout();

        $response = $this->get(route($this->getRouteName().'.edit', $model));

        $response->assertRedirect(route('login'));
    }

    public function test_store_requires_authentication(): void
    {
        $data = $this->getFormData();

        auth()->logout();

        $response = $this->post(route($this->getRouteName().'.store'), $data);

        $response->assertRedirect(route('login'));
    }

    public function test_update_requires_authentication(): void
    {
        $modelClass = $this->getModelClass();
        $model = $modelClass::factory()->create();
        $data = $this->getFormData();

        auth()->logout();

        $response = $this->put(route($this->getRouteName().'.update', $model), $data);

        $response->assertRedirect(route('login'));
    }

    public function test_destroy_requires_authentication(): void
    {
        $modelClass = $this->getModelClass();
        $model = $modelClass::factory()->create();

        auth()->logout();

        $response = $this->delete(route($this->getRouteName().'.destroy', $model));

        $response->assertRedirect(route('login'));
    }

    // ========== Authenticated CRUD Tests ==========

    public function test_index_page_displays(): void
    {
        $modelClass = $this->getModelClass();
        $modelClass::factory()->count(3)->create();

        $response = $this->get(route($this->getRouteName().'.index'));

        $response->assertOk()
            ->assertViewIs($this->getIndexView());
    }

    public function test_show_page_displays(): void
    {
        $modelClass = $this->getModelClass();
        $model = $modelClass::factory()->create();

        $response = $this->get(route($this->getRouteName().'.show', $model));

        $response->assertOk()
            ->assertViewIs($this->getShowView());
    }

    public function test_create_page_displays(): void
    {
        $response = $this->get(route($this->getRouteName().'.create'));

        $response->assertOk()
            ->assertViewIs($this->getCreateView());
    }

    public function test_edit_page_displays(): void
    {
        $modelClass = $this->getModelClass();
        $model = $modelClass::factory()->create();

        $response = $this->get(route($this->getRouteName().'.edit', $model));

        $response->assertOk()
            ->assertViewIs($this->getEditView());
    }

    public function test_store_creates_and_redirects(): void
    {
        $data = $this->getFormData();

        $response = $this->post(route($this->getRouteName().'.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas($this->getTableName(), $this->getDatabaseAssertions($data));
    }

    public function test_update_modifies_and_redirects(): void
    {
        $modelClass = $this->getModelClass();
        $model = $modelClass::factory()->create();
        $data = $this->getFormData();

        $response = $this->put(route($this->getRouteName().'.update', $model), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas($this->getTableName(), array_merge(['id' => $model->id], $this->getDatabaseAssertions($data)));
    }

    public function test_destroy_deletes_and_redirects(): void
    {
        $modelClass = $this->getModelClass();
        $model = $modelClass::factory()->create();

        $response = $this->delete(route($this->getRouteName().'.destroy', $model));

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

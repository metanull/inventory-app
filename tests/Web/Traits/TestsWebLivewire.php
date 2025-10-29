<?php

namespace Tests\Web\Traits;

/**
 * Trait for testing Livewire components
 */
trait TestsWebLivewire
{
    abstract protected function getComponentClass(): string;

    public function test_component_can_render(): void
    {
        $component = \Livewire\Livewire::test($this->getComponentClass());

        $component->assertStatus(200);
    }

    public function test_component_can_load_data(): void
    {
        $modelClass = $this->getModelClass();
        $models = $modelClass::factory()->count(3)->create();

        $component = \Livewire\Livewire::test($this->getComponentClass());

        foreach ($models as $model) {
            $component->assertSee($this->getIdentifier($model));
        }
    }

    public function test_component_can_sort(): void
    {
        if (! method_exists($this, 'getSortableFields')) {
            $this->markTestSkipped('Component does not support sorting');
        }

        $component = \Livewire\Livewire::test($this->getComponentClass());

        foreach ($this->getSortableFields() as $field) {
            $component->call('sortBy', $field)
                ->assertStatus(200);
        }
    }

    public function test_component_can_filter(): void
    {
        if (! method_exists($this, 'getFilterableFields')) {
            $this->markTestSkipped('Component does not support filtering');
        }

        $component = \Livewire\Livewire::test($this->getComponentClass());

        foreach ($this->getFilterableFields() as $field => $value) {
            $component->set($field, $value)
                ->assertStatus(200);
        }
    }

    abstract protected function getModelClass(): string;

    abstract protected function getIdentifier($model): string;
}

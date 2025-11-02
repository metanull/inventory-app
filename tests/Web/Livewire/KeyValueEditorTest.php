<?php

namespace Tests\Web\Livewire;

use App\Livewire\KeyValueEditor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KeyValueEditorTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_can_render(): void
    {
        $component = Livewire::test(KeyValueEditor::class);

        $component->assertOk();
    }

    public function test_component_initializes_with_empty_pair(): void
    {
        $component = Livewire::test(KeyValueEditor::class);

        $component->assertSet('pairs', [['key' => '', 'value' => '']]);
    }

    public function test_component_loads_initial_data(): void
    {
        $initialData = [
            'author' => 'John Doe',
            'version' => '1.0',
        ];

        $component = Livewire::test(KeyValueEditor::class, [
            'initialData' => $initialData,
        ]);

        $this->assertCount(2, $component->get('pairs'));
        $this->assertEquals('author', $component->get('pairs')[0]['key']);
        $this->assertEquals('John Doe', $component->get('pairs')[0]['value']);
        $this->assertEquals('version', $component->get('pairs')[1]['key']);
        $this->assertEquals('1.0', $component->get('pairs')[1]['value']);
    }

    public function test_component_can_add_pair(): void
    {
        $component = Livewire::test(KeyValueEditor::class);

        $initialCount = count($component->get('pairs'));
        $component->call('addPair');

        $this->assertCount($initialCount + 1, $component->get('pairs'));
    }

    public function test_component_can_remove_pair(): void
    {
        $component = Livewire::test(KeyValueEditor::class);
        $component->call('addPair');
        $component->call('addPair');

        $this->assertCount(3, $component->get('pairs'));

        $component->call('removePair', 1);

        $this->assertCount(2, $component->get('pairs'));
    }

    public function test_component_maintains_one_empty_pair_when_all_removed(): void
    {
        $component = Livewire::test(KeyValueEditor::class);

        $this->assertCount(1, $component->get('pairs'));

        $component->call('removePair', 0);

        $this->assertCount(1, $component->get('pairs'));
        $this->assertEquals('', $component->get('pairs')[0]['key']);
        $this->assertEquals('', $component->get('pairs')[0]['value']);
    }

    public function test_component_handles_json_values(): void
    {
        $initialData = [
            'tags' => ['tag1', 'tag2'],
            'metadata' => ['nested' => 'value'],
        ];

        $component = Livewire::test(KeyValueEditor::class, [
            'initialData' => $initialData,
        ]);

        $this->assertEquals('tags', $component->get('pairs')[0]['key']);
        $this->assertJson($component->get('pairs')[0]['value']);
        $this->assertEquals('metadata', $component->get('pairs')[1]['key']);
        $this->assertJson($component->get('pairs')[1]['value']);
    }

    public function test_component_sets_component_name(): void
    {
        $component = Livewire::test(KeyValueEditor::class, [
            'componentName' => 'custom_field',
        ]);

        $component->assertSet('componentName', 'custom_field');
    }

    public function test_component_uses_default_component_name(): void
    {
        $component = Livewire::test(KeyValueEditor::class);

        $component->assertSet('componentName', 'extra');
    }

    public function test_component_can_update_pair_values(): void
    {
        $component = Livewire::test(KeyValueEditor::class);

        $component->set('pairs.0.key', 'test_key');
        $component->set('pairs.0.value', 'test_value');

        $this->assertEquals('test_key', $component->get('pairs')[0]['key']);
        $this->assertEquals('test_value', $component->get('pairs')[0]['value']);
    }

    public function test_json_to_array_handles_null(): void
    {
        $component = Livewire::test(KeyValueEditor::class, [
            'initialData' => null,
        ]);

        $this->assertCount(1, $component->get('pairs'));
        $this->assertEquals('', $component->get('pairs')[0]['key']);
    }

    public function test_json_to_array_handles_empty_array(): void
    {
        $component = Livewire::test(KeyValueEditor::class, [
            'initialData' => [],
        ]);

        $this->assertCount(1, $component->get('pairs'));
        $this->assertEquals('', $component->get('pairs')[0]['key']);
    }

    public function test_component_handles_string_values(): void
    {
        $initialData = [
            'simple' => 'text',
            'number' => '123',
        ];

        $component = Livewire::test(KeyValueEditor::class, [
            'initialData' => $initialData,
        ]);

        $this->assertEquals('text', $component->get('pairs')[0]['value']);
        $this->assertEquals('123', $component->get('pairs')[1]['value']);
    }
}

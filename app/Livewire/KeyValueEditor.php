<?php

namespace App\Livewire;

use Livewire\Component;

class KeyValueEditor extends Component
{
    public array $pairs = [];

    public string $componentName = '';

    public ?string $maxDepth = '1';

    public function mount(?array $initialData = null, string $componentName = 'extra'): void
    {
        $this->componentName = $componentName;
        $this->pairs = $this->jsonToArray($initialData);
        if (empty($this->pairs)) {
            $this->addPair();
        }
    }

    public function addPair(): void
    {
        $this->pairs[] = ['key' => '', 'value' => ''];
    }

    public function removePair(int $index): void
    {
        unset($this->pairs[$index]);
        $this->pairs = array_values($this->pairs);

        // Ensure at least one empty pair exists
        if (empty($this->pairs)) {
            $this->addPair();
        }
    }

    public function jsonToArray(?array $data): array
    {
        if (! $data) {
            return [];
        }

        $result = [];
        foreach ($data as $key => $value) {
            $result[] = [
                'key' => $key,
                'value' => is_array($value) ? json_encode($value) : $value,
            ];
        }

        return $result;
    }

    public function render()
    {
        return view('livewire.key-value-editor');
    }
}

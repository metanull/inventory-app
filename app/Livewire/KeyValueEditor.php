<?php

namespace App\Livewire;

use Illuminate\View\View;
use Livewire\Component;

class KeyValueEditor extends Component
{
    /** @var array<int, array{key: string, value: string}> */
    public array $pairs = [];

    public string $componentName = '';

    /** @param array<string, mixed>|null $initialData */
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

    /**
     * @param  array<string, mixed>|null  $data
     * @return array<int, array{key: string, value: string}>
     */
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

    public function render(): View
    {
        return view('livewire.key-value-editor');
    }
}

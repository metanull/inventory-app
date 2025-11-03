<?php

namespace App\Http\Requests\Traits;

/**
 * Trait for Form Requests that handle Livewire key-value editor pairs
 *
 * This trait provides a method to convert key-value pairs from Livewire
 * components into JSON format for validation and storage.
 */
trait PreparesPairsForValidation
{
    /**
     * Prepare key-value pairs data for validation
     *
     * Handles both Livewire pairs format and direct array input,
     * converting to JSON for storage.
     *
     * @param  string  $fieldName  The field name to process (default: 'extra')
     */
    protected function preparePairsField(string $fieldName = 'extra'): void
    {
        // Handle Livewire key-value editor component data
        if ($this->has('pairs')) {
            $data = [];
            foreach ($this->input('pairs', []) as $pair) {
                if (! empty($pair['key'])) {
                    $data[$pair['key']] = $pair['value'];
                }
            }
            $this->merge([$fieldName => empty($data) ? null : json_encode($data)]);

            return;
        }

        // Keep existing array-to-JSON conversion for backward compatibility
        if ($this->has($fieldName) && is_array($this->{$fieldName})) {
            $this->merge([
                $fieldName => empty($this->{$fieldName}) ? null : json_encode($this->{$fieldName}),
            ]);
        }
    }
}

<?php

namespace App\Rules;

use App\Support\Includes\AllowList;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates comma-separated include parameter values against the AllowList.
 *
 * This rule validates that all comma-separated values in the include parameter
 * are valid relationship names for the specified entity type.
 *
 * @example new IncludeRule('item') validates against AllowList::for('item')
 */
class IncludeRule implements ValidationRule
{
    /**
     * Create a new rule instance.
     *
     * @param  string  $entity  The entity key (e.g., 'item', 'partner', 'collection')
     */
    public function __construct(
        public readonly string $entity
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === '' || $value === null) {
            return;
        }

        $allowed = $this->getAllowed();
        $parts = array_filter(array_map('trim', explode(',', (string) $value)));
        $invalid = array_diff($parts, $allowed);

        if (! empty($invalid)) {
            $fail('Invalid include value(s): '.implode(', ', $invalid).
                  '. Allowed: '.implode(', ', $allowed));
        }
    }

    /**
     * Get the allowed include values for this entity.
     *
     * @return array<int, string>
     */
    public function getAllowed(): array
    {
        return AllowList::for($this->entity);
    }
}

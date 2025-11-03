<?php

namespace App\Traits;

use App\Services\JsonNormalizationService;

/**
 * Trait for models that have JSON content fields
 *
 * This trait provides methods to handle JSON content in model attributes,
 * including normalization to associative arrays for consistent access.
 */
trait HasJsonFields
{
    /**
     * Get the JSON normalization service instance
     */
    protected function getJsonNormalizer(): JsonNormalizationService
    {
        return app(JsonNormalizationService::class);
    }

    /**
     * Normalize a JSON field value to an associative array
     *
     * @param  string  $field  The field name containing JSON data
     * @return array The normalized array
     */
    protected function normalizedJson(string $field): array
    {
        return $this->getJsonNormalizer()->normalize($this->{$field});
    }

    /**
     * Boot the trait
     */
    protected static function bootHasJsonFields(): void
    {
        // Add any model events or initialization here if needed
    }
}

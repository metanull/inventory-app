<?php

namespace App\Services;

/**
 * Service for normalizing JSON data
 *
 * This service provides functionality to normalize various JSON data formats
 * (strings, objects, arrays) into consistent associative arrays for use in
 * models and components.
 */
class JsonNormalizationService
{
    /**
     * Normalize a value to an associative array
     *
     * @param  mixed  $value  The value to normalize (string, object, array, null)
     * @param  bool  $emptyAsArray  Whether to return empty array for null/empty values
     * @return array<string, mixed>|null The normalized array or null based on $emptyAsArray
     */
    public function normalize($value, bool $emptyAsArray = true): ?array
    {
        if (is_null($value)) {
            return $emptyAsArray ? [] : null;
        }

        if (is_array($value)) {
            /** @var array<string, mixed> $typed */
            $typed = $value;

            return $typed;
        }

        if (is_object($value)) {
            $decoded = json_decode((string) json_encode($value), true);
            /** @var array<string, mixed> $typed */
            $typed = is_array($decoded) ? $decoded : [];

            return $typed;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            /** @var array<string, mixed> $typed */
            $typed = is_array($decoded) ? $decoded : [];

            return $typed;
        }

        return [];
    }
}

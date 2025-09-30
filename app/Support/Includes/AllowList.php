<?php

namespace App\Support\Includes;

/**
 * Central registry for valid include keys per entity.
 */
class AllowList
{
    /**
     * Get the allow-list of include keys for an entity.
     *
     * @param  string  $entity  Lowercase resource/entity key (e.g. 'item', 'partner')
     * @return array<int, string>
     */
    public static function for(string $entity): array
    {
        return match ($entity) {
            'item' => [
                'partner', 'country', 'project', 'collection',
                'parent', 'children', 'itemImages',
                'artists', 'workshops', 'tags', 'translations',
                'attachedToCollections',
            ],
            'itemImage' => [
                'item',
            ],
            'collection' => [
                'language', 'context', 'translations', 'partners', 'items', 'attachedItems',
            ],
            'partner' => [
                'country', 'items', 'pictures',
            ],
            'country' => [
                'items', 'partners',
            ],
            'project' => [
                'context', 'language',
            ],
            'contact' => [
                'translations',
            ],
            'province' => [
                'translations',
            ],
            'location' => [
                'translations',
            ],
            'address' => [
                'country', 'translations',
            ],
            'context' => [
                // Context model has no specific relationships
            ],
            'theme' => [
                'translations', 'subthemes', 'subthemes.translations',
            ],
            'available_image' => [
                // AvailableImage model has no relationships
            ],
            'itemImage' => [
                'item',
            ],
            default => [],
        };
    }
}

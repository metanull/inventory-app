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
                'artists', 'workshops', 'tags', 'translations',
                'pictures', 'galleries',
            ],
            'collection' => [
                'language', 'context', 'translations', 'partners', 'items',
            ],
            'gallery' => [
                'translations', 'partners', 'items', 'details',
            ],
            'exhibition' => [
                'translations', 'partners',
            ],
            'partner' => [
                'country', 'items', 'pictures',
            ],
            'country' => [
                'items', 'partners',
            ],
            'detail' => [
                'item', 'translations', 'pictures', 'galleries',
            ],
            'picture' => [
                'translations', 'pictureable',
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
                'translations',
            ],
            'theme' => [
                'translations', 'subthemes', 'subthemes.translations',
            ],
            default => [],
        };
    }
}

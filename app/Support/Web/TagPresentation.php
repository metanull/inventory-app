<?php

namespace App\Support\Web;

use App\Models\Tag;

class TagPresentation
{
    public static function badgeColor(?string $category): string
    {
        return match ($category) {
            'keyword' => 'blue',
            'material' => 'teal',
            'artist' => 'purple',
            'dynasty' => 'yellow',
            'subject' => 'indigo',
            'type' => 'gray',
            'filter' => 'red',
            'image-type' => 'green',
            default => 'gray',
        };
    }

    public static function label(Tag $tag): string
    {
        return $tag->description ?: $tag->internal_name;
    }
}

<?php

namespace App\Filament\Support;

use Filament\Support\Colors\Color;

class EntityColor
{
    public static function token(string $entity): string
    {
        return config("app_entities.colors.{$entity}", 'gray');
    }

    /**
     * @return array{50: string, 100: string, 200: string, 300: string, 400: string, 500: string, 600: string, 700: string, 800: string, 900: string, 950: string}
     */
    public static function palette(string $entity): array
    {
        return match (self::token($entity)) {
            'amber' => Color::Amber,
            'blue' => Color::Blue,
            'cyan' => Color::Cyan,
            'emerald' => Color::Emerald,
            'fuchsia' => Color::Fuchsia,
            'gray' => Color::Gray,
            'green' => Color::Green,
            'indigo' => Color::Indigo,
            'lime' => Color::Lime,
            'orange' => Color::Orange,
            'pink' => Color::Pink,
            'purple' => Color::Purple,
            'red' => Color::Red,
            'rose' => Color::Rose,
            'sky' => Color::Sky,
            'slate' => Color::Slate,
            'stone' => Color::Stone,
            'teal' => Color::Teal,
            'violet' => Color::Violet,
            'yellow' => Color::Yellow,
            'zinc' => Color::Zinc,
            default => Color::Gray,
        };
    }
}

<?php

namespace App\Enums;

enum ItemType: string
{
    case OBJECT = 'object';
    case MONUMENT = 'monument';
    case DETAIL = 'detail';
    case PICTURE = 'picture';

    /**
     * Get the display label for the type.
     */
    public function label(): string
    {
        return match ($this) {
            self::OBJECT => 'Object',
            self::MONUMENT => 'Monument',
            self::DETAIL => 'Detail',
            self::PICTURE => 'Picture',
        };
    }

    /**
     * Get all types as options for forms.
     *
     * @return array<int, object{id: string, name: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $type) => (object) ['id' => $type->value, 'name' => $type->label()],
            self::cases()
        );
    }
}

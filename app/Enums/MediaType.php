<?php

namespace App\Enums;

enum MediaType: string
{
    case AUDIO = 'audio';
    case VIDEO = 'video';

    /**
     * Get the display label for the type.
     */
    public function label(): string
    {
        return match ($this) {
            self::AUDIO => 'Audio',
            self::VIDEO => 'Video',
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

<?php

namespace App\Filament\Support;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class TranslationFormSchema
{
    public static function languageField(): Select
    {
        return Select::make('language_id')
            ->label('Language')
            ->relationship('language', 'internal_name')
            ->searchable()
            ->preload()
            ->required();
    }

    public static function contextField(): Select
    {
        return Select::make('context_id')
            ->label('Context')
            ->relationship('context', 'internal_name')
            ->searchable()
            ->preload()
            ->required();
    }

    public static function nameField(): TextInput
    {
        return TextInput::make('name')
            ->required()
            ->maxLength(255);
    }

    public static function alternateNameField(): TextInput
    {
        return TextInput::make('alternate_name')
            ->maxLength(255);
    }

    public static function descriptionField(): Textarea
    {
        return Textarea::make('description')
            ->rows(4)
            ->columnSpanFull();
    }

    public static function backwardCompatibilityField(): TextInput
    {
        return TextInput::make('backward_compatibility')
            ->label('Legacy ID')
            ->maxLength(255)
            ->placeholder('Optional legacy identifier');
    }

    public static function extraField(): KeyValue
    {
        return KeyValue::make('extra')
            ->label('Extra metadata')
            ->afterStateHydrated(static function (KeyValue $component, mixed $state): void {
                if ($state instanceof \stdClass) {
                    $state = (array) $state;
                } elseif (is_string($state)) {
                    $decoded = json_decode($state, true);
                    $state = is_array($decoded) ? $decoded : [];
                }

                if (is_array($state)) {
                    // Flatten nested arrays to JSON strings so KeyValue can display them
                    $flat = array_map(
                        static fn (mixed $v): ?string => is_array($v) || $v instanceof \stdClass
                            ? json_encode($v)
                            : (is_null($v) ? null : (string) $v),
                        $state
                    );
                    $component->state($flat);
                }
            })
            ->columnSpanFull();
    }

    /**
     * @return array<int, Select|Textarea|TextInput>
     */
    public static function make(): array
    {
        return [
            static::languageField(),
            static::contextField(),
            static::nameField(),
            static::alternateNameField(),
            static::descriptionField(),
        ];
    }
}

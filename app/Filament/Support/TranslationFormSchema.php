<?php

namespace App\Filament\Support;

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

<?php

namespace App\Filament\Support;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class TranslationFormSchema
{
    /**
     * @return array<int, Select|Textarea|TextInput>
     */
    public static function make(): array
    {
        return [
            Select::make('language_id')
                ->label('Language')
                ->relationship('language', 'internal_name')
                ->searchable()
                ->preload()
                ->required(),
            Select::make('context_id')
                ->label('Context')
                ->relationship('context', 'internal_name')
                ->searchable()
                ->preload()
                ->required(),
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            TextInput::make('alternate_name')
                ->maxLength(255),
            Textarea::make('description')
                ->rows(4)
                ->columnSpanFull(),
        ];
    }
}

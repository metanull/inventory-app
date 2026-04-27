<?php

namespace App\Filament\Support;

use App\Models\Author;
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

    /**
     * Returns a searchable Select bound to the authors table.
     *
     * The search is server-side and limited to 50 results to keep the query bounded
     * even when the author catalogue grows large.
     */
    public static function authorSelectField(string $name, string $label): Select
    {
        return Select::make($name)
            ->label($label)
            ->nullable()
            ->searchable()
            ->getSearchResultsUsing(fn (string $search): array => Author::query()
                ->where('name', 'like', "%{$search}%")
                ->orWhere('internal_name', 'like', "%{$search}%")
                ->orderBy('name')
                ->limit(50)
                ->pluck('name', 'id')
                ->all()
            )
            ->getOptionLabelUsing(fn ($v): string => Author::find($v)?->name ?? $v);
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
                    // Filament's KeyValue maps each entry to a string key/value pair.
                    // Nested arrays or objects (which can appear when the model stores
                    // structured JSON) must be serialised back to JSON strings so that
                    // KeyValue can display them without a type error.
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

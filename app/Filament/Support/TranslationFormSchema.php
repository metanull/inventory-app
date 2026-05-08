<?php

namespace App\Filament\Support;

use App\Models\Author;
use App\Models\Collection;
use App\Models\Context;
use App\Models\Item;
use App\Models\Partner;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;

class TranslationFormSchema
{
    public static function languageField(): Select
    {
        return Select::make('language_id')
            ->label('Language')
            ->relationship('language', 'internal_name')
            ->searchable()
            ->required();
    }

    public static function contextField(): Select
    {
        return Select::make('context_id')
            ->label('Context')
            ->relationship('context', 'internal_name')
            ->searchable()
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

    public static function itemSelectField(
        string $name = 'item_id',
        string $label = 'Item',
        bool $required = true,
        bool $includeIdInSearch = false
    ): Select {
        $select = Select::make($name)
            ->label($label)
            ->searchable()
            ->getSearchResultsUsing(function (string $search) use ($includeIdInSearch): array {
                $query = Item::query()
                    ->where(function (Builder $query) use ($search, $includeIdInSearch): void {
                        $query->where('internal_name', 'like', "%{$search}%")
                            ->orWhere('backward_compatibility', 'like', "%{$search}%");

                        if ($includeIdInSearch) {
                            $query->orWhere('id', 'like', "%{$search}%");
                        }
                    })
                    ->orderBy('internal_name')
                    ->limit(50)
                    ->get();

                return $query->mapWithKeys(fn (Item $item): array => [
                    $item->id => static::legacyLabel($item->internal_name, $item->backward_compatibility),
                ])->all();
            })
            ->getOptionLabelUsing(function (mixed $value): string {
                $item = Item::find($value);

                return $item
                    ? static::legacyLabel($item->internal_name, $item->backward_compatibility)
                    : (string) $value;
            });

        return static::requiredOrNullable($select, $required);
    }

    public static function collectionSelectField(
        string $name = 'collection_id',
        string $label = 'Collection',
        bool $required = true,
        bool $includeIdInSearch = false
    ): Select {
        $select = Select::make($name)
            ->label($label)
            ->searchable()
            ->getSearchResultsUsing(function (string $search) use ($includeIdInSearch): array {
                $query = Collection::query()
                    ->where(function (Builder $query) use ($search, $includeIdInSearch): void {
                        $query->where('internal_name', 'like', "%{$search}%")
                            ->orWhere('backward_compatibility', 'like', "%{$search}%");

                        if ($includeIdInSearch) {
                            $query->orWhere('id', 'like', "%{$search}%");
                        }
                    })
                    ->orderBy('internal_name')
                    ->limit(50)
                    ->get();

                return $query->mapWithKeys(fn (Collection $collection): array => [
                    $collection->id => static::legacyLabel($collection->internal_name, $collection->backward_compatibility),
                ])->all();
            })
            ->getOptionLabelUsing(function (mixed $value): string {
                $collection = Collection::find($value);

                return $collection
                    ? static::legacyLabel($collection->internal_name, $collection->backward_compatibility)
                    : (string) $value;
            });

        return static::requiredOrNullable($select, $required);
    }

    public static function partnerSelectField(
        string $name = 'partner_id',
        string $label = 'Partner',
        bool $required = true,
        bool $includeIdInSearch = false
    ): Select {
        $select = Select::make($name)
            ->label($label)
            ->searchable()
            ->getSearchResultsUsing(function (string $search) use ($includeIdInSearch): array {
                $query = Partner::query()
                    ->where(function (Builder $query) use ($search, $includeIdInSearch): void {
                        $query->where('internal_name', 'like', "%{$search}%")
                            ->orWhere('backward_compatibility', 'like', "%{$search}%");

                        if ($includeIdInSearch) {
                            $query->orWhere('id', 'like', "%{$search}%");
                        }
                    })
                    ->orderBy('internal_name')
                    ->limit(50)
                    ->get();

                return $query->mapWithKeys(fn (Partner $partner): array => [
                    $partner->id => static::legacyLabel($partner->internal_name, $partner->backward_compatibility),
                ])->all();
            })
            ->getOptionLabelUsing(function (mixed $value): string {
                $partner = Partner::find($value);

                return $partner
                    ? static::legacyLabel($partner->internal_name, $partner->backward_compatibility)
                    : (string) $value;
            });

        return static::requiredOrNullable($select, $required);
    }

    public static function contextSelectField(
        string $name = 'context_id',
        string $label = 'Context',
        bool $required = true
    ): Select {
        $select = Select::make($name)
            ->label($label)
            ->searchable()
            ->getSearchResultsUsing(fn (string $search): array => Context::query()
                ->where('internal_name', 'like', "%{$search}%")
                ->orderBy('internal_name')
                ->limit(50)
                ->pluck('internal_name', 'id')
                ->all()
            )
            ->getOptionLabelUsing(fn ($value): string => Context::find($value)?->internal_name ?? (string) $value);

        return static::requiredOrNullable($select, $required);
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

    private static function requiredOrNullable(Select $select, bool $required): Select
    {
        return $required ? $select->required() : $select->nullable();
    }

    private static function legacyLabel(string $internalName, ?string $backwardCompatibility): string
    {
        return $backwardCompatibility
            ? "{$internalName} [{$backwardCompatibility}]"
            : $internalName;
    }
}

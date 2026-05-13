<?php

namespace App\Filament\Resources\ItemResource\RelationManagers;

use App\Filament\Resources\CollectionResource;
use App\Filament\Resources\ContextResource;
use App\Filament\Resources\LanguageResource;
use App\Filament\Support\CollectionDisplayLabel;
use App\Filament\Support\CollectionItemAppearance;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CollectionAppearancesRelationManager extends RelationManager
{
    protected static string $relationship = 'attachedToCollections';

    protected static ?string $recordTitleAttribute = 'internal_name';

    protected static ?string $title = 'Collection appearances';

    public function table(Table $table): Table
    {
        return $table
            ->inverseRelationship('attachedItems')
            ->modifyQueryUsing(fn (Builder $query): Builder => CollectionDisplayLabel::withDisplayLabel(
                $query->with([
                    'context:id,internal_name',
                    'language:id,internal_name',
                    'parent:id,internal_name',
                ])
            ))
            ->defaultSort('internal_name', 'asc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                CollectionDisplayLabel::displayLabelColumn()
                    ->url(fn ($record): ?string => $this->getAuthorizedUrl($record, CollectionResource::class)),
                TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('parent.internal_name')
                    ->label('Parent collection')
                    ->sortable()
                    ->toggleable()
                    ->url(fn ($record): ?string => $record->parent
                        ? $this->getAuthorizedUrl($record->parent, CollectionResource::class)
                        : null),
                CollectionItemAppearance::displayOrderColumn(),
                CollectionItemAppearance::contextualTextPreviewColumn(),
                CollectionItemAppearance::contextualTextLanguagesColumn(),
                TextColumn::make('context.internal_name')
                    ->label('Context')
                    ->sortable()
                    ->toggleable()
                    ->url(fn ($record): ?string => $record->context
                        ? $this->getAuthorizedUrl($record->context, ContextResource::class)
                        : null),
                TextColumn::make('language.internal_name')
                    ->label('Language')
                    ->sortable()
                    ->toggleable()
                    ->url(fn ($record): ?string => $record->language
                        ? $this->getAuthorizedUrl($record->language, LanguageResource::class)
                        : null),
                TextColumn::make('internal_name')
                    ->label('Internal name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                CollectionItemAppearance::viewAppearanceTextAction(),
            ]);
    }

    private function getAuthorizedUrl(mixed $record, string $resourceClass): ?string
    {
        return auth()->user()?->can('view', $record)
            ? $resourceClass::getUrl('view', ['record' => $record])
            : null;
    }
}

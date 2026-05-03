<?php

namespace App\Filament\Resources\ItemResource\RelationManagers;

use App\Filament\Resources\CollectionResource;
use App\Filament\Resources\ContextResource;
use App\Filament\Resources\LanguageResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DisplayedInCollectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachedToCollections';

    protected static ?string $recordTitleAttribute = 'internal_name';

    protected static ?string $title = 'Displayed in';

    public function table(Table $table): Table
    {
        return $table
            ->inverseRelationship('attachedItems')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'context:id,internal_name',
                'language:id,internal_name',
            ]))
            ->defaultSort('internal_name', 'asc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('internal_name')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record): ?string => $this->getAuthorizedUrl($record, CollectionResource::class)),
                TextColumn::make('type')
                    ->badge()
                    ->sortable(),
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
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ]);
    }

    private function getAuthorizedUrl(mixed $record, string $resourceClass): ?string
    {
        return auth()->user()?->can('view', $record)
            ? $resourceClass::getUrl('view', ['record' => $record])
            : null;
    }
}

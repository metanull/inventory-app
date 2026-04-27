<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Enums\ItemType;
use App\Filament\Resources\CollectionResource;
use App\Filament\Resources\ItemResource;
use App\Filament\Resources\PartnerResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $recordTitleAttribute = 'internal_name';

    protected static ?string $title = 'Items';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'partner:id,internal_name',
                'collection:id,internal_name',
            ]))
            ->defaultSort('internal_name', 'asc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('internal_name')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record): ?string => auth()->user()?->can('view', $record)
                        ? ItemResource::getUrl('view', ['record' => $record])
                        : null),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (?ItemType $state): ?string => $state?->label())
                    ->sortable(),
                TextColumn::make('partner.internal_name')
                    ->label('Partner')
                    ->sortable()
                    ->url(fn ($record): ?string => $record->partner
                        ? (auth()->user()?->can('view', $record->partner) ? PartnerResource::getUrl('view', ['record' => $record->partner]) : null)
                        : null),
                TextColumn::make('collection.internal_name')
                    ->label('Collection')
                    ->sortable()
                    ->url(fn ($record): ?string => $record->collection
                        ? (auth()->user()?->can('view', $record->collection) ? CollectionResource::getUrl('view', ['record' => $record->collection]) : null)
                        : null),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ]);
    }
}

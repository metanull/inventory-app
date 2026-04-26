<?php

namespace App\Filament\Resources\PartnerResource\RelationManagers;

use App\Enums\PartnerLevel;
use App\Filament\Resources\CollectionResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CollectionParticipationsRelationManager extends RelationManager
{
    protected static string $relationship = 'collections';

    protected static ?string $recordTitleAttribute = 'internal_name';

    protected static ?string $title = 'Collection participations';

    public function table(Table $table): Table
    {
        return $table
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
                    ->url(fn ($record) => CollectionResource::getUrl('view', ['record' => $record])),
                TextColumn::make('type')
                    ->sortable(),
                TextColumn::make('pivot.collection_type')
                    ->label('Collection type')
                    ->sortable(),
                TextColumn::make('pivot.level')
                    ->label('Level')
                    ->formatStateUsing(fn (?string $state): ?string => $state ? PartnerLevel::from($state)->label() : null)
                    ->sortable(),
                IconColumn::make('pivot.visible')
                    ->label('Visible')
                    ->boolean(),
            ]);
    }
}

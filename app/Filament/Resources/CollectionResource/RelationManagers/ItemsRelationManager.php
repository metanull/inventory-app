<?php

namespace App\Filament\Resources\CollectionResource\RelationManagers;

use App\Enums\ItemType;
use App\Filament\Resources\ItemResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Actions\DetachBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachedItems';

    protected static ?string $recordTitleAttribute = 'internal_name';

    protected static ?string $title = 'Items';

    public function table(Table $table): Table
    {
        return $table
            ->inverseRelationship('attachedToCollections')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'partner:id,internal_name',
                'project:id,internal_name',
            ]))
            ->defaultSort('internal_name', 'asc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('internal_name')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => ItemResource::getUrl('view', ['record' => $record])),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (?ItemType $state): ?string => $state?->label())
                    ->sortable(),
                TextColumn::make('partner.internal_name')
                    ->label('Partner')
                    ->sortable(),
                TextColumn::make('project.internal_name')
                    ->label('Project')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                DetachAction::make(),
            ])
            ->bulkActions([
                DetachBulkAction::make(),
            ]);
    }
}

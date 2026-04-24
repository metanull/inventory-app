<?php

namespace App\Filament\Resources\ItemResource\RelationManagers;

use App\Enums\ItemType;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChildItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'children';

    protected static ?string $recordTitleAttribute = 'internal_name';

    protected static ?string $title = 'Child items';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'partner:id,internal_name',
                'country:id,internal_name',
            ]))
            ->defaultSort('display_order', 'asc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('internal_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (?ItemType $state): ?string => $state?->label())
                    ->sortable(),
                TextColumn::make('partner.internal_name')
                    ->label('Partner')
                    ->sortable(),
                TextColumn::make('country.internal_name')
                    ->label('Country')
                    ->sortable(),
                TextColumn::make('display_order')
                    ->label('Order')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        ItemType::DETAIL->value => ItemType::DETAIL->label(),
                        ItemType::PICTURE->value => ItemType::PICTURE->label(),
                    ]),
            ]);
    }
}

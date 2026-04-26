<?php

namespace App\Filament\Resources\CollectionResource\RelationManagers;

use App\Enums\PartnerLevel;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Actions\DetachBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PartnersRelationManager extends RelationManager
{
    protected static string $relationship = 'partners';

    protected static ?string $recordTitleAttribute = 'internal_name';

    protected static ?string $title = 'Partners';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'country:id,internal_name',
            ]))
            ->defaultSort('internal_name', 'asc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('internal_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('pivot.level')
                    ->label('Level')
                    ->formatStateUsing(fn (?string $state): ?string => $state ? PartnerLevel::from($state)->label() : null)
                    ->sortable(),
                TextColumn::make('country.internal_name')
                    ->label('Country')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->form(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('level')
                            ->label('Level')
                            ->options(
                                collect(PartnerLevel::cases())
                                    ->mapWithKeys(fn (PartnerLevel $level) => [$level->value => $level->label()])
                                    ->all()
                            )
                            ->required(),
                        Hidden::make('collection_type')
                            ->default('collection'),
                    ]),
            ])
            ->actions([
                DetachAction::make(),
            ])
            ->bulkActions([
                DetachBulkAction::make(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\GlossaryResource\RelationManagers;

use App\Filament\Resources\GlossaryResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Actions\DetachBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SynonymsRelationManager extends RelationManager
{
    protected static string $relationship = 'synonyms';

    protected static ?string $recordTitleAttribute = 'internal_name';

    protected static ?string $title = 'Synonyms';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('internal_name', 'asc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('internal_name')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record): ?string => auth()->user()?->can('view', $record)
                        ? GlossaryResource::getUrl('view', ['record' => $record])
                        : null),
                TextColumn::make('backward_compatibility')
                    ->label('Legacy code')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                AttachAction::make()
                    ->recordSelectSearchColumns(['internal_name', 'backward_compatibility'])
                    ->recordSelectOptionsQuery(fn ($query) => $query->orderBy('internal_name')),
            ])
            ->actions([
                DetachAction::make(),
            ])
            ->bulkActions([
                DetachBulkAction::make(),
            ]);
    }
}

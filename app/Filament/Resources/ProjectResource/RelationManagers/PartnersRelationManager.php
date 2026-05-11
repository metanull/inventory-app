<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Filament\Resources\CountryResource;
use App\Filament\Resources\PartnerResource;
use App\Filament\Support\PartnerDisplayLabel;
use Filament\Resources\RelationManagers\RelationManager;
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
            ->modifyQueryUsing(fn (Builder $query): Builder => PartnerDisplayLabel::withDisplayLabel(
                $query->with([
                    'country:id,internal_name',
                ])
            ))
            ->defaultSort('internal_name', 'asc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                PartnerDisplayLabel::displayLabelColumn()
                    ->url(fn ($record): ?string => auth()->user()?->can('view', $record)
                        ? PartnerResource::getUrl('view', ['record' => $record])
                        : null),
                TextColumn::make('type')
                    ->sortable(),
                TextColumn::make('country.internal_name')
                    ->label('Country')
                    ->sortable()
                    ->url(fn ($record): ?string => $record->country
                        ? (auth()->user()?->can('view', $record->country) ? CountryResource::getUrl('view', ['record' => $record->country]) : null)
                        : null),
                TextColumn::make('internal_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ]);
    }
}

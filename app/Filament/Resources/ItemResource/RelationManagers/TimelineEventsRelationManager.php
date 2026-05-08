<?php

namespace App\Filament\Resources\ItemResource\RelationManagers;

use App\Filament\Resources\TimelineEventResource;
use App\Filament\Resources\TimelineResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TimelineEventsRelationManager extends RelationManager
{
    protected static string $relationship = 'timelineEvents';

    protected static ?string $recordTitleAttribute = 'internal_name';

    protected static ?string $title = 'Timeline events';

    public function table(Table $table): Table
    {
        return $table
            ->inverseRelationship('items')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'timeline:id,internal_name',
            ]))
            ->defaultSort('timeline.internal_name', 'asc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('timeline.internal_name')
                    ->label('Timeline')
                    ->sortable()
                    ->url(fn ($record): ?string => $record->timeline && auth()->user()?->can('view', $record->timeline)
                        ? TimelineResource::getUrl('view', ['record' => $record->timeline])
                        : null),
                TextColumn::make('internal_name')
                    ->label('Event')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record): ?string => auth()->user()?->can('view', $record)
                        ? TimelineEventResource::getUrl('view', ['record' => $record])
                        : null),
                TextColumn::make('year_from')
                    ->label('Year from')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('year_to')
                    ->label('Year to')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('pivot.display_order')
                    ->label('Display order')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ]);
    }
}

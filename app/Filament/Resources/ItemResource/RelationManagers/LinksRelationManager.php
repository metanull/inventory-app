<?php

namespace App\Filament\Resources\ItemResource\RelationManagers;

use App\Enums\ItemType;
use App\Filament\Resources\ContextResource;
use App\Filament\Resources\ItemResource;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LinksRelationManager extends RelationManager
{
    protected static string $relationship = 'outgoingLinks';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'Links';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('target_id')
                    ->label('Target item')
                    ->relationship('target', 'internal_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('context_id')
                    ->label('Context')
                    ->relationship('context', 'internal_name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'target:id,internal_name,type',
                'context:id,internal_name',
            ]))
            ->defaultSort('created_at', 'asc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('target.internal_name')
                    ->label('Target item')
                    ->sortable()
                    ->url(fn ($record): ?string => $record->target
                        ? (auth()->user()?->can('view', $record->target) ? ItemResource::getUrl('view', ['record' => $record->target]) : null)
                        : null),
                TextColumn::make('target.type')
                    ->label('Target type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ItemType ? $state->label() : (string) $state),
                TextColumn::make('context.internal_name')
                    ->label('Context')
                    ->sortable()
                    ->url(fn ($record): ?string => $record->context
                        ? (auth()->user()?->can('view', $record->context) ? ContextResource::getUrl('view', ['record' => $record->context]) : null)
                        : null),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}

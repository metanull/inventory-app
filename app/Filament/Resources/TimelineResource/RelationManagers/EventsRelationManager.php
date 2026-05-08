<?php

namespace App\Filament\Resources\TimelineResource\RelationManagers;

use App\Filament\Resources\TimelineEventResource;
use App\Models\TimelineEvent;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventsRelationManager extends RelationManager
{
    protected static string $relationship = 'events';

    protected static ?string $recordTitleAttribute = 'internal_name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('internal_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('year_from')
                    ->label('Year from')
                    ->numeric()
                    ->integer()
                    ->nullable(),
                TextInput::make('year_to')
                    ->label('Year to')
                    ->numeric()
                    ->integer()
                    ->nullable(),
                TextInput::make('year_from_ah')
                    ->label('Year from (AH)')
                    ->numeric()
                    ->integer()
                    ->nullable(),
                TextInput::make('year_to_ah')
                    ->label('Year to (AH)')
                    ->numeric()
                    ->integer()
                    ->nullable(),
                DatePicker::make('date_from')
                    ->label('Date from')
                    ->nullable(),
                DatePicker::make('date_to')
                    ->label('Date to')
                    ->nullable(),
                TextInput::make('display_order')
                    ->label('Display order')
                    ->numeric()
                    ->integer()
                    ->nullable(),
                TextInput::make('backward_compatibility')
                    ->label('Legacy code')
                    ->maxLength(255)
                    ->nullable(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('display_order', 'asc')
            ->columns([
                TextColumn::make('internal_name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('year_from')
                    ->label('Year from')
                    ->sortable(),
                TextColumn::make('year_to')
                    ->label('Year to')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('display_order')
                    ->label('Order')
                    ->sortable(),
                TextColumn::make('date_from')
                    ->label('Date from')
                    ->date()
                    ->toggleable(),
                TextColumn::make('date_to')
                    ->label('Date to')
                    ->date()
                    ->toggleable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                Action::make('viewEvent')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (TimelineEvent $record): string => TimelineEventResource::getUrl('view', ['record' => $record])),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}

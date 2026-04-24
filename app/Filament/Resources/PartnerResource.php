<?php

namespace App\Filament\Resources;

use App\Enums\ItemType;
use App\Filament\Concerns\HasBackwardCompatibilityColumn;
use App\Filament\Concerns\HasInternalNameColumn;
use App\Filament\Concerns\HasTimestampsColumns;
use App\Filament\Concerns\HasUuidColumn;
use App\Filament\Resources\PartnerResource\Pages\CreatePartner;
use App\Filament\Resources\PartnerResource\Pages\EditPartner;
use App\Filament\Resources\PartnerResource\Pages\ListPartner;
use App\Filament\Resources\PartnerResource\Pages\ViewPartner;
use App\Filament\Resources\PartnerResource\RelationManagers\CollectionParticipationsRelationManager;
use App\Filament\Resources\PartnerResource\RelationManagers\OwnedItemsRelationManager;
use App\Filament\Resources\PartnerResource\RelationManagers\TranslationsRelationManager;
use App\Models\Partner;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PartnerResource extends Resource
{
    use HasBackwardCompatibilityColumn;
    use HasInternalNameColumn;
    use HasTimestampsColumns;
    use HasUuidColumn;

    protected static ?string $model = Partner::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $recordTitleAttribute = 'internal_name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('internal_name')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->options([
                        'museum' => 'Museum',
                        'institution' => 'Institution',
                        'individual' => 'Individual',
                        'school' => 'School',
                    ])
                    ->required(),
                TextInput::make('backward_compatibility')
                    ->label('Legacy code')
                    ->maxLength(255),
                Select::make('country_id')
                    ->label('Country')
                    ->relationship('country', 'internal_name')
                    ->searchable()
                    ->preload(),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                TextInput::make('map_zoom')
                    ->numeric()
                    ->integer(),
                Select::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'internal_name')
                    ->searchable()
                    ->preload(),
                Select::make('monument_item_id')
                    ->label('Monument item')
                    ->relationship(
                        name: 'monumentItem',
                        titleAttribute: 'internal_name',
                        modifyQueryUsing: fn (Builder $query): Builder => $query->where('type', ItemType::MONUMENT->value),
                    )
                    ->searchable()
                    ->preload(),
                Toggle::make('visible')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('internal_name', 'asc')
            ->columns([
                static::internalNameColumn(),
                TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('country.internal_name')
                    ->label('Country')
                    ->sortable(),
                TextColumn::make('project.internal_name')
                    ->label('Project')
                    ->sortable(),
                IconColumn::make('visible')
                    ->boolean()
                    ->sortable(),
                static::backwardCompatibilityColumn(),
                static::uuidColumn(),
                ...static::timestampsColumns(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('internal_name'),
                TextEntry::make('type'),
                TextEntry::make('country.internal_name')
                    ->label('Country'),
                TextEntry::make('project.internal_name')
                    ->label('Project'),
                TextEntry::make('monumentItem.internal_name')
                    ->label('Monument item'),
                IconEntry::make('visible')
                    ->boolean(),
                TextEntry::make('latitude'),
                TextEntry::make('longitude'),
                TextEntry::make('map_zoom')
                    ->label('Map zoom'),
                TextEntry::make('backward_compatibility')
                    ->label('Legacy code'),
                TextEntry::make('id')
                    ->label('UUID'),
                TextEntry::make('created_at')
                    ->label('Created')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->label('Updated')
                    ->dateTime(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            OwnedItemsRelationManager::class,
            CollectionParticipationsRelationManager::class,
            TranslationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPartner::route('/'),
            'create' => CreatePartner::route('/create'),
            'edit' => EditPartner::route('/{record}/edit'),
            'view' => ViewPartner::route('/{record}'),
        ];
    }
}

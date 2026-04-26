<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasBackwardCompatibilityColumn;
use App\Filament\Concerns\HasInternalNameColumn;
use App\Filament\Concerns\HasTimestampsColumns;
use App\Filament\Concerns\HasUuidColumn;
use App\Filament\Resources\GlossaryResource\Pages\CreateGlossary;
use App\Filament\Resources\GlossaryResource\Pages\EditGlossary;
use App\Filament\Resources\GlossaryResource\Pages\ListGlossary;
use App\Filament\Resources\GlossaryResource\Pages\ViewGlossary;
use App\Filament\Resources\GlossaryResource\RelationManagers\SpellingsRelationManager;
use App\Filament\Resources\GlossaryResource\RelationManagers\TranslationsRelationManager;
use App\Models\Glossary;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Table;

class GlossaryResource extends Resource
{
    use HasBackwardCompatibilityColumn;
    use HasInternalNameColumn;
    use HasTimestampsColumns;
    use HasUuidColumn;

    protected static ?string $model = Glossary::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Shared data';

    protected static ?string $recordTitleAttribute = 'internal_name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['internal_name', 'backward_compatibility'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('internal_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('backward_compatibility')
                    ->label('Legacy code')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('internal_name', 'asc')
            ->columns([
                static::internalNameColumn()
                    ->searchable(['internal_name', 'id']),
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
            TranslationsRelationManager::class,
            SpellingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGlossary::route('/'),
            'create' => CreateGlossary::route('/create'),
            'edit' => EditGlossary::route('/{record}/edit'),
            'view' => ViewGlossary::route('/{record}'),
        ];
    }
}

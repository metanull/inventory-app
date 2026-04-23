<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasBackwardCompatibilityColumn;
use App\Filament\Concerns\HasInternalNameColumn;
use App\Filament\Concerns\HasTimestampsColumns;
use App\Filament\Concerns\HasUuidColumn;
use App\Filament\Resources\ProjectResource\Pages\CreateProject;
use App\Filament\Resources\ProjectResource\Pages\EditProject;
use App\Filament\Resources\ProjectResource\Pages\ListProject;
use App\Filament\Resources\ProjectResource\Pages\ViewProject;
use App\Filament\Support\EntityColor;
use App\Models\Project;
use Filament\Forms\Components\DatePicker;
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

class ProjectResource extends Resource
{
    use HasBackwardCompatibilityColumn;
    use HasInternalNameColumn;
    use HasTimestampsColumns;
    use HasUuidColumn;

    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    protected static ?string $navigationGroup = 'Reference Data';

    protected static ?string $recordTitleAttribute = 'internal_name';

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
                Select::make('context_id')
                    ->label('Context')
                    ->relationship('context', 'internal_name')
                    ->searchable()
                    ->preload(),
                Select::make('language_id')
                    ->label('Language')
                    ->relationship('language', 'internal_name')
                    ->searchable()
                    ->preload(),
                Toggle::make('is_enabled')
                    ->label('Enabled'),
                Toggle::make('is_launched')
                    ->label('Launched'),
                DatePicker::make('launch_date')
                    ->label('Launch date'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                static::internalNameColumn(),
                static::backwardCompatibilityColumn(),
                TextColumn::make('context.internal_name')
                    ->label('Context')
                    ->toggleable(),
                TextColumn::make('language.internal_name')
                    ->label('Language')
                    ->toggleable(),
                IconColumn::make('is_enabled')
                    ->label('Enabled')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_launched')
                    ->label('Launched')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('launch_date')
                    ->label('Launch date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
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
                TextEntry::make('context.internal_name')
                    ->label('Context'),
                TextEntry::make('language.internal_name')
                    ->label('Language'),
                IconEntry::make('is_enabled')
                    ->label('Enabled')
                    ->boolean(),
                IconEntry::make('is_launched')
                    ->label('Launched')
                    ->boolean(),
                TextEntry::make('launch_date')
                    ->label('Launch date')
                    ->date(),
                TextEntry::make('id')
                    ->label('UUID')
                    ->copyable(),
                TextEntry::make('created_at')
                    ->label('Created')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->label('Updated')
                    ->dateTime(),
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return EntityColor::palette('projects');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProject::route('/'),
            'create' => CreateProject::route('/create'),
            'view' => ViewProject::route('/{record}'),
            'edit' => EditProject::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasBackwardCompatibilityColumn;
use App\Filament\Concerns\HasInternalNameColumn;
use App\Filament\Concerns\HasTimestampsColumns;
use App\Filament\Concerns\HasUuidColumn;
use App\Filament\Resources\ProjectResource\Pages\ListProject;
use App\Models\Project;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
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
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                static::internalNameColumn(),
                static::backwardCompatibilityColumn(),
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
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProject::route('/'),
        ];
    }
}

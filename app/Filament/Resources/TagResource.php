<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasBackwardCompatibilityColumn;
use App\Filament\Concerns\HasTimestampsColumns;
use App\Filament\Concerns\HasUuidColumn;
use App\Filament\Resources\TagResource\Pages\CreateTag;
use App\Filament\Resources\TagResource\Pages\EditTag;
use App\Filament\Resources\TagResource\Pages\ListTag;
use App\Filament\Resources\TagResource\Pages\ViewTag;
use App\Filament\Support\EntityColor;
use App\Models\Tag;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TagResource extends Resource
{
    use HasBackwardCompatibilityColumn;
    use HasTimestampsColumns;
    use HasUuidColumn;

    private const CATEGORY_OPTIONS = [
        'keyword' => 'Keyword',
        'material' => 'Material',
        'artist' => 'Artist',
        'dynasty' => 'Dynasty',
        'subject' => 'Subject',
        'type' => 'Type',
        'filter' => 'Filter',
        'image-type' => 'Image type',
    ];

    protected static ?string $model = Tag::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Shared data';

    protected static ?string $recordTitleAttribute = 'description';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('internal_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('description')
                    ->required()
                    ->maxLength(255),
                Select::make('category')
                    ->options(static::categoryOptions())
                    ->searchable()
                    ->preload(),
                Select::make('language_id')
                    ->label('Language')
                    ->relationship('language', 'internal_name')
                    ->searchable()
                    ->preload(),
                TextInput::make('backward_compatibility')
                    ->label('Legacy code')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('description', 'asc')
            ->columns([
                TextColumn::make('description')
                    ->label('Tag')
                    ->badge()
                    ->color(fn (Tag $record): array => EntityColor::palette($record->category ?? 'tags'))
                    ->searchable(['description', 'internal_name'])
                    ->sortable(),
                TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => static::categoryOptions()[$state] ?? 'Uncategorised')
                    ->color(fn (Tag $record): array => EntityColor::palette($record->category ?? 'tags'))
                    ->sortable(),
                TextColumn::make('internal_name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                static::backwardCompatibilityColumn(),
                TextColumn::make('language.internal_name')
                    ->label('Language')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                static::uuidColumn(),
                ...static::timestampsColumns(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options(static::categoryOptions()),
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
                TextEntry::make('description')
                    ->label('Tag'),
                TextEntry::make('category')
                    ->formatStateUsing(fn (?string $state): string => static::categoryOptions()[$state] ?? 'Uncategorised'),
                TextEntry::make('internal_name'),
                TextEntry::make('language.internal_name')
                    ->label('Language'),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('language');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTag::route('/'),
            'create' => CreateTag::route('/create'),
            'edit' => EditTag::route('/{record}/edit'),
            'view' => ViewTag::route('/{record}'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function categoryOptions(): array
    {
        return self::CATEGORY_OPTIONS;
    }
}

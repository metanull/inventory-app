<?php

namespace App\Filament\Resources;

use App\Enums\Permission;
use App\Filament\Concerns\HasBackwardCompatibilityColumn;
use App\Filament\Concerns\HasInternalNameColumn;
use App\Filament\Concerns\HasTimestampsColumns;
use App\Filament\Concerns\HasTranslationCoverageFilters;
use App\Filament\Concerns\HasUuidColumn;
use App\Filament\Resources\CollectionResource\Pages\CreateCollection;
use App\Filament\Resources\CollectionResource\Pages\EditCollection;
use App\Filament\Resources\CollectionResource\Pages\ListCollection;
use App\Filament\Resources\CollectionResource\Pages\ViewCollection;
use App\Filament\Resources\CollectionResource\RelationManagers\ChildCollectionsRelationManager;
use App\Filament\Resources\CollectionResource\RelationManagers\ImagesRelationManager;
use App\Filament\Resources\CollectionResource\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\CollectionResource\RelationManagers\PartnersRelationManager;
use App\Filament\Resources\CollectionResource\RelationManagers\TranslationsRelationManager;
use App\Models\Collection;
use App\Models\Project;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class CollectionResource extends Resource
{
    use HasBackwardCompatibilityColumn;
    use HasInternalNameColumn;
    use HasTimestampsColumns;
    use HasTranslationCoverageFilters;
    use HasUuidColumn;

    private const TYPE_OPTIONS = [
        'collection' => 'Collection',
        'exhibition' => 'Exhibition',
        'gallery' => 'Gallery',
        'theme' => 'Theme',
        'exhibition trail' => 'Exhibition trail',
        'itinerary' => 'Itinerary',
        'location' => 'Location',
        'subtheme' => 'Subtheme',
        'region' => 'Region',
    ];

    protected static ?string $model = Collection::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $recordTitleAttribute = 'internal_name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['internal_name', 'backward_compatibility', 'translations.title', 'parent.internal_name', 'country.internal_name'];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo(Permission::VIEW_DATA->value) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('internal_name')
                    ->required()
                    ->maxLength(255),
                Select::make('type')
                    ->options(self::TYPE_OPTIONS)
                    ->required(),
                TextInput::make('backward_compatibility')
                    ->label('Legacy code')
                    ->maxLength(255),
                Select::make('language_id')
                    ->label('Language')
                    ->relationship('language', 'internal_name')
                    ->searchable(),
                Select::make('context_id')
                    ->label('Context')
                    ->relationship('context', 'internal_name')
                    ->searchable(),
                Select::make('parent_id')
                    ->label('Parent collection')
                    ->relationship(
                        name: 'parent',
                        titleAttribute: 'internal_name',
                        modifyQueryUsing: fn (Builder $query, ?Collection $record): Builder => $record
                            ? $query->excludingDescendantsOf($record->id)
                            : $query,
                    )
                    ->searchable()
                    ->nullable(),
                Select::make('country_id')
                    ->label('Country')
                    ->relationship('country', 'internal_name')
                    ->searchable(),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                TextInput::make('map_zoom')
                    ->label('Map zoom')
                    ->numeric()
                    ->integer(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => static::withFallbackExists(
                $query->with([
                    'parent:id,internal_name',
                    'context:id,internal_name',
                    'language:id,internal_name',
                ])
            ))
            ->defaultSort('internal_name', 'asc')
            ->columns([
                static::internalNameColumn(),
                static::fallbackTranslationColumn(),
                TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('parent.internal_name')
                    ->label('Parent')
                    ->sortable()
                    ->toggleable()
                    ->url(fn ($record): ?string => $record->parent
                        ? (auth()->user()?->can('view', $record->parent) ? static::getUrl('view', ['record' => $record->parent]) : null)
                        : null),
                TextColumn::make('context.internal_name')
                    ->label('Context')
                    ->sortable()
                    ->toggleable()
                    ->url(fn ($record): ?string => $record->context
                        ? (auth()->user()?->can('view', $record->context) ? ContextResource::getUrl('view', ['record' => $record->context]) : null)
                        : null),
                TextColumn::make('language.internal_name')
                    ->label('Language')
                    ->sortable()
                    ->toggleable()
                    ->url(fn ($record): ?string => $record->language
                        ? (auth()->user()?->can('view', $record->language) ? LanguageResource::getUrl('view', ['record' => $record->language]) : null)
                        : null),
                static::backwardCompatibilityColumn(),
                static::uuidColumn(),
                ...static::timestampsColumns(),
            ])
            ->filters([
                ...static::translationCoverageFilters(),
                SelectFilter::make('type')
                    ->options(self::TYPE_OPTIONS),
                SelectFilter::make('parent_id')
                    ->label('Parent')
                    ->relationship('parent', 'internal_name')
                    ->searchable(),
                SelectFilter::make('partner')
                    ->label('Partner')
                    ->relationship('partners', 'internal_name')
                    ->searchable()
                    ->query(fn (Builder $query, array $data): Builder => $data['value']
                        ? $query->whereHas('partners', fn (Builder $q): Builder => $q->where('partners.id', $data['value']))
                        : $query),
                SelectFilter::make('project')
                    ->label('Project')
                    ->getSearchResultsUsing(fn (string $search): array => Project::query()
                        ->where('internal_name', 'like', "%{$search}%")
                        ->orWhere('backward_compatibility', 'like', "%{$search}%")
                        ->orderBy('internal_name')
                        ->limit(50)
                        ->pluck('internal_name', 'id')
                        ->all()
                    )
                    ->getOptionLabelUsing(fn ($value): string => Project::find($value)?->internal_name ?? $value)
                    ->searchable()
                    ->query(fn (Builder $query, array $data): Builder => $data['value']
                        ? $query->whereHas('items', fn (Builder $q): Builder => $q->where('project_id', $data['value']))
                        : $query),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('changeParent')
                    ->label('Change parent')
                    ->icon('heroicon-o-arrow-uturn-up')
                    ->form(fn (Collection $record): array => [
                        Select::make('parent_id')
                            ->label('New parent collection')
                            ->nullable()
                            ->getSearchResultsUsing(fn (string $search): array => Collection::query()
                                ->excludingDescendantsOf($record->id)
                                ->where('internal_name', 'like', "%{$search}%")
                                ->orWhere('backward_compatibility', 'like', "%{$search}%")
                                ->orderBy('internal_name')
                                ->limit(50)
                                ->pluck('internal_name', 'id')
                                ->all()
                            )
                            ->getOptionLabelUsing(fn ($value): string => Collection::find($value)?->internal_name ?? $value)
                            ->searchable(),
                    ])
                    ->action(function (Collection $record, array $data): void {
                        try {
                            $record->parent_id = $data['parent_id'] ?? null;
                            $record->save();

                            Notification::make()
                                ->success()
                                ->title('Parent updated')
                                ->send();
                        } catch (\RuntimeException $e) {
                            logger()->warning('CollectionResource: changeParent failed', [
                                'collection_id' => $record->id,
                                'new_parent_id' => $data['parent_id'] ?? null,
                                'error' => $e->getMessage(),
                            ]);

                            Notification::make()
                                ->danger()
                                ->title('Cannot change parent')
                                ->body('The selected parent would create a circular hierarchy. Please choose a different parent.')
                                ->send();
                        }
                    }),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkAction::make('moveToParent')
                    ->label('Move to parent')
                    ->icon('heroicon-o-arrow-uturn-up')
                    ->form([
                        Select::make('parent_id')
                            ->label('New parent collection')
                            ->nullable()
                            ->getSearchResultsUsing(fn (string $search): array => Collection::query()
                                ->where('internal_name', 'like', "%{$search}%")
                                ->orWhere('backward_compatibility', 'like', "%{$search}%")
                                ->orderBy('internal_name')
                                ->limit(50)
                                ->pluck('internal_name', 'id')
                                ->all()
                            )
                            ->getOptionLabelUsing(fn ($value): string => Collection::find($value)?->internal_name ?? $value)
                            ->searchable(),
                    ])
                    ->action(function (EloquentCollection $records, array $data): void {
                        $errors = [];
                        foreach ($records as $record) {
                            try {
                                $record->parent_id = $data['parent_id'] ?? null;
                                $record->save();
                            } catch (\RuntimeException $e) {
                                logger()->warning('CollectionResource: moveToParent failed', [
                                    'collection_id' => $record->id,
                                    'new_parent_id' => $data['parent_id'] ?? null,
                                    'error' => $e->getMessage(),
                                ]);
                                $errors[] = $record->internal_name;
                            }
                        }

                        if (empty($errors)) {
                            Notification::make()
                                ->success()
                                ->title('Collections moved')
                                ->send();
                        } else {
                            Notification::make()
                                ->danger()
                                ->title('Some collections could not be moved')
                                ->body('The following collections would create a circular hierarchy: '.implode(', ', $errors))
                                ->send();
                        }
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('internal_name'),
                TextEntry::make('type'),
                TextEntry::make('parent.internal_name')
                    ->label('Parent')
                    ->url(fn ($record): ?string => $record->parent
                        ? (auth()->user()?->can('view', $record->parent) ? static::getUrl('view', ['record' => $record->parent]) : null)
                        : null),
                TextEntry::make('context.internal_name')
                    ->label('Context')
                    ->url(fn ($record): ?string => $record->context
                        ? (auth()->user()?->can('view', $record->context) ? ContextResource::getUrl('view', ['record' => $record->context]) : null)
                        : null),
                TextEntry::make('language.internal_name')
                    ->label('Language')
                    ->url(fn ($record): ?string => $record->language
                        ? (auth()->user()?->can('view', $record->language) ? LanguageResource::getUrl('view', ['record' => $record->language]) : null)
                        : null),
                TextEntry::make('country.internal_name')
                    ->label('Country')
                    ->url(fn ($record): ?string => $record->country
                        ? (auth()->user()?->can('view', $record->country) ? CountryResource::getUrl('view', ['record' => $record->country]) : null)
                        : null),
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
            ChildCollectionsRelationManager::class,
            ItemsRelationManager::class,
            PartnersRelationManager::class,
            ImagesRelationManager::class,
            TranslationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCollection::route('/'),
            'create' => CreateCollection::route('/create'),
            'edit' => EditCollection::route('/{record}/edit'),
            'view' => ViewCollection::route('/{record}'),
        ];
    }
}

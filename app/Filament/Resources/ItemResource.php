<?php

namespace App\Filament\Resources;

use App\Enums\ItemType;
use App\Enums\Permission;
use App\Filament\Concerns\HasBackwardCompatibilityColumn;
use App\Filament\Concerns\HasInternalNameColumn;
use App\Filament\Concerns\HasTimestampsColumns;
use App\Filament\Concerns\HasTranslationCoverageFilters;
use App\Filament\Concerns\HasUuidColumn;
use App\Filament\Resources\ItemResource\Pages\CreateItem;
use App\Filament\Resources\ItemResource\Pages\EditItem;
use App\Filament\Resources\ItemResource\Pages\ListItem;
use App\Filament\Resources\ItemResource\Pages\ViewItem;
use App\Filament\Resources\ItemResource\RelationManagers\ChildItemsRelationManager;
use App\Filament\Resources\ItemResource\RelationManagers\ImagesRelationManager;
use App\Filament\Resources\ItemResource\RelationManagers\LinksRelationManager;
use App\Filament\Resources\ItemResource\RelationManagers\PicturesRelationManager;
use App\Filament\Resources\ItemResource\RelationManagers\TagsRelationManager;
use App\Filament\Resources\ItemResource\RelationManagers\TranslationsRelationManager;
use App\Models\Collection;
use App\Models\Country;
use App\Models\Item;
use App\Models\Partner;
use App\Models\Project;
use App\Models\Tag;
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
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class ItemResource extends Resource
{
    use HasBackwardCompatibilityColumn;
    use HasInternalNameColumn;
    use HasTimestampsColumns;
    use HasTranslationCoverageFilters;
    use HasUuidColumn;

    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $recordTitleAttribute = 'internal_name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['internal_name', 'backward_compatibility', 'translations.name', 'translations.alternate_name'];
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
                Select::make('type')
                    ->options(
                        collect(ItemType::cases())
                            ->mapWithKeys(fn (ItemType $type) => [$type->value => $type->label()])
                            ->all()
                    )
                    ->required(),
                TextInput::make('internal_name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('backward_compatibility')
                    ->label('Legacy code')
                    ->maxLength(255),
                Select::make('parent_id')
                    ->label('Parent item')
                    ->relationship(
                        name: 'parent',
                        titleAttribute: 'internal_name',
                        modifyQueryUsing: fn (Builder $query, ?Item $record): Builder => $record
                            ? $query->excludingDescendantsOf($record->id)
                            : $query,
                    )
                    ->searchable()
                    ->nullable(),
                Select::make('partner_id')
                    ->label('Partner')
                    ->relationship('partner', 'internal_name')
                    ->searchable()
                    ->nullable(),
                Select::make('country_id')
                    ->label('Country')
                    ->relationship('country', 'internal_name')
                    ->searchable()
                    ->nullable(),
                Select::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'internal_name')
                    ->searchable()
                    ->nullable(),
                TextInput::make('display_order')
                    ->label('Display order')
                    ->numeric()
                    ->integer()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => static::withFallbackExists(
                $query->with([
                    'parent:id,internal_name',
                    'partner:id,internal_name',
                    'country:id,internal_name',
                    'project:id,internal_name',
                ])
            ))
            ->defaultSort('internal_name', 'asc')
            ->columns([
                static::internalNameColumn(),
                static::fallbackTranslationColumn(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (?ItemType $state): ?string => $state?->label())
                    ->sortable(),
                TextColumn::make('parent.internal_name')
                    ->label('Parent')
                    ->sortable()
                    ->toggleable()
                    ->url(fn ($record): ?string => $record->parent
                        ? (auth()->user()?->can('view', $record->parent) ? static::getUrl('view', ['record' => $record->parent]) : null)
                        : null),
                TextColumn::make('partner.internal_name')
                    ->label('Partner')
                    ->sortable()
                    ->toggleable()
                    ->url(fn ($record): ?string => $record->partner
                        ? (auth()->user()?->can('view', $record->partner) ? PartnerResource::getUrl('view', ['record' => $record->partner]) : null)
                        : null),
                TextColumn::make('country.internal_name')
                    ->label('Country')
                    ->sortable()
                    ->toggleable()
                    ->url(fn ($record): ?string => $record->country
                        ? (auth()->user()?->can('view', $record->country) ? CountryResource::getUrl('view', ['record' => $record->country]) : null)
                        : null),
                TextColumn::make('project.internal_name')
                    ->label('Project')
                    ->sortable()
                    ->toggleable()
                    ->url(fn ($record): ?string => $record->project
                        ? (auth()->user()?->can('view', $record->project) ? ProjectResource::getUrl('view', ['record' => $record->project]) : null)
                        : null),
                static::backwardCompatibilityColumn(),
                static::uuidColumn(),
                ...static::timestampsColumns(),
            ])
            ->filters([
                ...static::translationCoverageFilters(),
                SelectFilter::make('type')
                    ->options(
                        collect(ItemType::cases())
                            ->mapWithKeys(fn (ItemType $type) => [$type->value => $type->label()])
                            ->all()
                    ),
                SelectFilter::make('partner_id')
                    ->label('Partner')
                    ->relationship('partner', 'internal_name')
                    ->getSearchResultsUsing(fn (string $search): array => Partner::query()
                        ->where('internal_name', 'like', "%{$search}%")
                        ->orderBy('internal_name')
                        ->limit(50)
                        ->pluck('internal_name', 'id')
                        ->all()
                    )
                    ->getOptionLabelUsing(fn ($value): string => Partner::find($value)?->internal_name ?? $value)
                    ->searchable(),
                SelectFilter::make('collection')
                    ->label('Collection')
                    ->getSearchResultsUsing(fn (string $search): array => Collection::query()
                        ->where('internal_name', 'like', "%{$search}%")
                        ->orderBy('internal_name')
                        ->limit(50)
                        ->pluck('internal_name', 'id')
                        ->all()
                    )
                    ->getOptionLabelUsing(fn ($value): string => Collection::find($value)?->internal_name ?? $value)
                    ->searchable()
                    ->query(fn (Builder $query, array $data): Builder => $data['value']
                        ? $query->whereHas('attachedToCollections', fn (Builder $q): Builder => $q->where('collections.id', $data['value']))
                        : $query),
                SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'internal_name')
                    ->getSearchResultsUsing(fn (string $search): array => Project::query()
                        ->where('internal_name', 'like', "%{$search}%")
                        ->orderBy('internal_name')
                        ->limit(50)
                        ->pluck('internal_name', 'id')
                        ->all()
                    )
                    ->getOptionLabelUsing(fn ($value): string => Project::find($value)?->internal_name ?? $value)
                    ->searchable(),
                SelectFilter::make('country_id')
                    ->label('Country')
                    ->relationship('country', 'internal_name')
                    ->getSearchResultsUsing(fn (string $search): array => Country::query()
                        ->where('internal_name', 'like', "%{$search}%")
                        ->orderBy('internal_name')
                        ->limit(50)
                        ->pluck('internal_name', 'id')
                        ->all()
                    )
                    ->getOptionLabelUsing(fn ($value): string => Country::find($value)?->internal_name ?? $value)
                    ->searchable(),
                SelectFilter::make('tags')
                    ->label('Tag')
                    ->multiple()
                    ->getSearchResultsUsing(fn (string $search): array => Tag::query()
                        ->where('internal_name', 'like', "%{$search}%")
                        ->orderBy('internal_name')
                        ->limit(50)
                        ->pluck('internal_name', 'id')
                        ->all()
                    )
                    ->getOptionLabelUsing(fn ($value): string => Tag::find($value)?->internal_name ?? $value)
                    ->query(fn (Builder $query, array $data): Builder => ! empty($data['values'])
                        ? $query->withAnyTags($data['values'])
                        : $query),
                TernaryFilter::make('top_level_only')
                    ->label('Hierarchy')
                    ->trueLabel('Top-level only')
                    ->falseLabel('All items')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNull('parent_id'),
                        false: fn (Builder $query): Builder => $query,
                        blank: fn (Builder $query): Builder => $query,
                    ),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('changeParent')
                    ->label('Change parent')
                    ->icon('heroicon-o-arrow-uturn-up')
                    ->form([
                        Select::make('parent_id')
                            ->label('New parent item')
                            ->nullable()
                            ->getSearchResultsUsing(fn (string $search): array => Item::query()
                                ->where('internal_name', 'like', "%{$search}%")
                                ->orderBy('internal_name')
                                ->limit(50)
                                ->pluck('internal_name', 'id')
                                ->all()
                            )
                            ->getOptionLabelUsing(fn ($value): string => Item::find($value)?->internal_name ?? $value)
                            ->searchable(),
                    ])
                    ->action(function (Item $record, array $data): void {
                        try {
                            $record->parent_id = $data['parent_id'] ?? null;
                            $record->save();

                            Notification::make()
                                ->success()
                                ->title('Parent updated')
                                ->send();
                        } catch (\RuntimeException $e) {
                            logger()->warning('ItemResource: changeParent failed', [
                                'item_id' => $record->id,
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
                            ->label('New parent item')
                            ->nullable()
                            ->getSearchResultsUsing(fn (string $search): array => Item::query()
                                ->where('internal_name', 'like', "%{$search}%")
                                ->orderBy('internal_name')
                                ->limit(50)
                                ->pluck('internal_name', 'id')
                                ->all()
                            )
                            ->getOptionLabelUsing(fn ($value): string => Item::find($value)?->internal_name ?? $value)
                            ->searchable(),
                    ])
                    ->action(function (EloquentCollection $records, array $data): void {
                        $errors = [];
                        foreach ($records as $record) {
                            try {
                                $record->parent_id = $data['parent_id'] ?? null;
                                $record->save();
                            } catch (\RuntimeException $e) {
                                logger()->warning('ItemResource: moveToParent failed', [
                                    'item_id' => $record->id,
                                    'new_parent_id' => $data['parent_id'] ?? null,
                                    'error' => $e->getMessage(),
                                ]);
                                $errors[] = $record->internal_name;
                            }
                        }

                        if (empty($errors)) {
                            Notification::make()
                                ->success()
                                ->title('Items moved')
                                ->send();
                        } else {
                            Notification::make()
                                ->danger()
                                ->title('Some items could not be moved')
                                ->body('The following items would create a circular hierarchy: '.implode(', ', $errors))
                                ->send();
                        }
                    })
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('attachToCollection')
                    ->label('Attach to collection')
                    ->icon('heroicon-o-archive-box')
                    ->form([
                        Select::make('collection_id')
                            ->label('Collection')
                            ->required()
                            ->getSearchResultsUsing(fn (string $search): array => Collection::query()
                                ->where('internal_name', 'like', "%{$search}%")
                                ->orderBy('internal_name')
                                ->limit(50)
                                ->pluck('internal_name', 'id')
                                ->all()
                            )
                            ->getOptionLabelUsing(fn ($value): string => Collection::find($value)?->internal_name ?? $value)
                            ->searchable(),
                    ])
                    ->action(function (EloquentCollection $records, array $data): void {
                        $collectionId = $data['collection_id'];
                        foreach ($records as $record) {
                            $record->attachedToCollections()->syncWithoutDetaching([$collectionId]);
                        }

                        Notification::make()
                            ->success()
                            ->title('Items attached to collection')
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('attachTag')
                    ->label('Attach tag')
                    ->icon('heroicon-o-tag')
                    ->form([
                        Select::make('tag_id')
                            ->label('Tag')
                            ->required()
                            ->getSearchResultsUsing(fn (string $search): array => Tag::query()
                                ->where('internal_name', 'like', "%{$search}%")
                                ->orderBy('internal_name')
                                ->limit(50)
                                ->pluck('internal_name', 'id')
                                ->all()
                            )
                            ->getOptionLabelUsing(fn ($value): string => Tag::find($value)?->internal_name ?? $value)
                            ->searchable(),
                    ])
                    ->action(function (EloquentCollection $records, array $data): void {
                        $tagId = $data['tag_id'];
                        foreach ($records as $record) {
                            $record->tags()->syncWithoutDetaching([$tagId]);
                        }

                        Notification::make()
                            ->success()
                            ->title('Tag attached to items')
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                TextEntry::make('internal_name'),
                TextEntry::make('type')
                    ->formatStateUsing(fn (?ItemType $state): ?string => $state?->label()),
                TextEntry::make('parent.internal_name')
                    ->label('Parent')
                    ->url(fn ($record): ?string => $record->parent
                        ? (auth()->user()?->can('view', $record->parent) ? static::getUrl('view', ['record' => $record->parent]) : null)
                        : null),
                TextEntry::make('partner.internal_name')
                    ->label('Partner')
                    ->url(fn ($record): ?string => $record->partner
                        ? (auth()->user()?->can('view', $record->partner) ? PartnerResource::getUrl('view', ['record' => $record->partner]) : null)
                        : null),
                TextEntry::make('country.internal_name')
                    ->label('Country')
                    ->url(fn ($record): ?string => $record->country
                        ? (auth()->user()?->can('view', $record->country) ? CountryResource::getUrl('view', ['record' => $record->country]) : null)
                        : null),
                TextEntry::make('project.internal_name')
                    ->label('Project')
                    ->url(fn ($record): ?string => $record->project
                        ? (auth()->user()?->can('view', $record->project) ? ProjectResource::getUrl('view', ['record' => $record->project]) : null)
                        : null),
                TextEntry::make('display_order')
                    ->label('Display order'),
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
            ChildItemsRelationManager::class,
            PicturesRelationManager::class,
            ImagesRelationManager::class,
            TranslationsRelationManager::class,
            LinksRelationManager::class,
            TagsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListItem::route('/'),
            'create' => CreateItem::route('/create'),
            'edit' => EditItem::route('/{record}/edit'),
            'view' => ViewItem::route('/{record}'),
        ];
    }
}

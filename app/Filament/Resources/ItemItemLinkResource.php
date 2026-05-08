<?php

namespace App\Filament\Resources;

use App\Enums\ItemType;
use App\Enums\Permission;
use App\Filament\Concerns\HasTimestampsColumns;
use App\Filament\Concerns\HasUuidColumn;
use App\Filament\Resources\ItemItemLinkResource\Pages\CreateItemItemLink;
use App\Filament\Resources\ItemItemLinkResource\Pages\EditItemItemLink;
use App\Filament\Resources\ItemItemLinkResource\Pages\ListItemItemLink;
use App\Filament\Resources\ItemItemLinkResource\Pages\ViewItemItemLink;
use App\Filament\Resources\ItemItemLinkResource\RelationManagers\TranslationsRelationManager;
use App\Filament\Support\TranslationFormSchema;
use App\Models\ItemItemLink;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section as InfolistSection;
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

class ItemItemLinkResource extends Resource
{
    use HasTimestampsColumns;
    use HasUuidColumn;

    protected static ?string $model = ItemItemLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Item Links';

    protected static ?string $recordTitleAttribute = 'id';

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
                TranslationFormSchema::itemSelectField(name: 'source_id', label: 'Source item'),
                TranslationFormSchema::itemSelectField(name: 'target_id', label: 'Target item'),
                TranslationFormSchema::contextSelectField(required: false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'source:id,internal_name,type,backward_compatibility',
                'target:id,internal_name,type,backward_compatibility',
                'context:id,internal_name',
            ]))
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('source.internal_name')
                    ->label('Source item')
                    ->sortable()
                    ->searchable()
                    ->url(fn ($record): ?string => $record->source
                        ? (auth()->user()?->can('view', $record->source) ? ItemResource::getUrl('view', ['record' => $record->source]) : null)
                        : null),
                TextColumn::make('source.type')
                    ->label('Source type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ItemType ? $state->label() : (string) $state)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('target.internal_name')
                    ->label('Target item')
                    ->sortable()
                    ->searchable()
                    ->url(fn ($record): ?string => $record->target
                        ? (auth()->user()?->can('view', $record->target) ? ItemResource::getUrl('view', ['record' => $record->target]) : null)
                        : null),
                TextColumn::make('target.type')
                    ->label('Target type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof ItemType ? $state->label() : (string) $state)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('context.internal_name')
                    ->label('Context')
                    ->sortable()
                    ->url(fn ($record): ?string => $record->context
                        ? (auth()->user()?->can('view', $record->context) ? ContextResource::getUrl('view', ['record' => $record->context]) : null)
                        : null),
                ...static::timestampsColumns(),
            ])
            ->filters([
                SelectFilter::make('context_id')
                    ->label('Context')
                    ->relationship('context', 'internal_name')
                    ->searchable(),
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
                InfolistSection::make('Link Details')
                    ->schema([
                        TextEntry::make('source.internal_name')
                            ->label('Source item')
                            ->url(fn ($record): ?string => $record->source
                                ? (auth()->user()?->can('view', $record->source) ? ItemResource::getUrl('view', ['record' => $record->source]) : null)
                                : null),
                        TextEntry::make('target.internal_name')
                            ->label('Target item')
                            ->url(fn ($record): ?string => $record->target
                                ? (auth()->user()?->can('view', $record->target) ? ItemResource::getUrl('view', ['record' => $record->target]) : null)
                                : null),
                        TextEntry::make('context.internal_name')
                            ->label('Context')
                            ->url(fn ($record): ?string => $record->context
                                ? (auth()->user()?->can('view', $record->context) ? ContextResource::getUrl('view', ['record' => $record->context]) : null)
                                : null),
                        TextEntry::make('id')
                            ->label('UUID'),
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Updated')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TranslationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListItemItemLink::route('/'),
            'create' => CreateItemItemLink::route('/create'),
            'edit' => EditItemItemLink::route('/{record}/edit'),
            'view' => ViewItemItemLink::route('/{record}'),
        ];
    }
}

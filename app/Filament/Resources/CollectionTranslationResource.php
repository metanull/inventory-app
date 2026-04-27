<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasTimestampsColumns;
use App\Filament\Resources\CollectionTranslationResource\Pages\CreateCollectionTranslation;
use App\Filament\Resources\CollectionTranslationResource\Pages\EditCollectionTranslation;
use App\Filament\Resources\CollectionTranslationResource\Pages\ListCollectionTranslation;
use App\Models\Collection;
use App\Models\CollectionTranslation;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;

class CollectionTranslationResource extends Resource
{
    use HasTimestampsColumns;

    protected static ?string $model = CollectionTranslation::class;

    protected static ?string $navigationIcon = 'heroicon-o-language';

    protected static ?string $navigationGroup = 'Translations';

    protected static ?string $navigationLabel = 'Collection Translations';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('collection_id')
                    ->label('Collection')
                    ->required()
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => Collection::query()
                        ->where('internal_name', 'like', "%{$search}%")
                        ->orderBy('internal_name')
                        ->limit(50)
                        ->pluck('internal_name', 'id')
                        ->all()
                    )
                    ->getOptionLabelUsing(fn ($v): string => Collection::find($v)?->internal_name ?? $v),
                Select::make('language_id')
                    ->label('Language')
                    ->relationship('language', 'internal_name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->unique(
                        table: 'collection_translations',
                        column: 'language_id',
                        modifyRuleUsing: fn (Unique $rule, Get $get, ?CollectionTranslation $record): Unique => $rule
                            ->where('collection_id', $get('collection_id') ?? '')
                            ->where('context_id', $get('context_id') ?? '')
                            ->ignore($record?->id),
                        ignoreRecord: true,
                    )
                    ->validationMessages(['unique' => 'A translation for this collection, language and context already exists.']),
                Select::make('context_id')
                    ->label('Context')
                    ->relationship('context', 'internal_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->rows(4)
                    ->columnSpanFull(),
                TextInput::make('url')
                    ->url()
                    ->nullable()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'collection:id,internal_name',
                'language:id,internal_name,is_default',
                'context:id,internal_name,is_default',
            ]))
            ->columns([
                TextColumn::make('collection.internal_name')
                    ->label('Collection')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('language.internal_name')
                    ->label('Language')
                    ->sortable()
                    ->badge()
                    ->color(fn (CollectionTranslation $r): string => $r->language?->is_default ? 'success' : 'gray'),
                TextColumn::make('context.internal_name')
                    ->label('Context')
                    ->sortable()
                    ->badge()
                    ->color(fn (CollectionTranslation $r): string => $r->context?->is_default ? 'success' : 'gray'),
                IconColumn::make('is_default_pair')
                    ->label('★')
                    ->tooltip('Default language + context pair')
                    ->getStateUsing(fn (CollectionTranslation $r): bool => (bool) ($r->language?->is_default && $r->context?->is_default))
                    ->trueIcon('heroicon-s-star')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                ...static::timestampsColumns(),
            ])
            ->filters([
                SelectFilter::make('language_id')
                    ->label('Language')
                    ->relationship('language', 'internal_name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('context_id')
                    ->label('Context')
                    ->relationship('context', 'internal_name')
                    ->searchable()
                    ->preload(),
                Filter::make('default_language')
                    ->label('Default language only')
                    ->query(fn (Builder $query): Builder => $query->whereHas('language', fn (Builder $q): Builder => $q->where('is_default', true))),
                Filter::make('default_context')
                    ->label('Default context only')
                    ->query(fn (Builder $query): Builder => $query->whereHas('context', fn (Builder $q): Builder => $q->where('is_default', true))),
                Filter::make('is_default_pair')
                    ->label('Default pair only')
                    ->query(fn (Builder $query): Builder => $query
                        ->whereHas('language', fn (Builder $q): Builder => $q->where('is_default', true))
                        ->whereHas('context', fn (Builder $q): Builder => $q->where('is_default', true))
                    ),
                Filter::make('missing_fallback')
                    ->label('Owner missing default translation')
                    ->query(fn (Builder $query): Builder => $query->whereHas('collection', fn (Builder $cq): Builder => $cq->whereDoesntHave('translations', fn (Builder $tq): Builder => $tq
                        ->whereHas('language', fn (Builder $lq): Builder => $lq->where('is_default', true))
                        ->whereHas('context', fn (Builder $dq): Builder => $dq->where('is_default', true))
                    ))),
                Filter::make('recently_updated')
                    ->label('Recently updated (30 days)')
                    ->query(fn (Builder $query): Builder => $query->where('updated_at', '>=', now()->subDays(30))),
            ])
            ->actions([
                Action::make('viewCollection')
                    ->label('View collection')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (CollectionTranslation $r): string => CollectionResource::getUrl('view', ['record' => $r->collection_id]))
                    ->openUrlInNewTab(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCollectionTranslation::route('/'),
            'create' => CreateCollectionTranslation::route('/create'),
            'edit' => EditCollectionTranslation::route('/{record}/edit'),
        ];
    }
}

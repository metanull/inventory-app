<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasTimestampsColumns;
use App\Filament\Resources\ItemTranslationResource\Pages\CreateItemTranslation;
use App\Filament\Resources\ItemTranslationResource\Pages\EditItemTranslation;
use App\Filament\Resources\ItemTranslationResource\Pages\ListItemTranslation;
use App\Filament\Support\TranslationFormSchema;
use App\Models\Item;
use App\Models\ItemTranslation;
use Filament\Forms\Components\Section;
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

class ItemTranslationResource extends Resource
{
    use HasTimestampsColumns;

    protected static ?string $model = ItemTranslation::class;

    protected static ?string $navigationIcon = 'heroicon-o-language';

    protected static ?string $navigationGroup = 'Translations';

    protected static ?string $navigationLabel = 'Item Translations';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('item_id')
                    ->label('Item')
                    ->required()
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => Item::query()
                        ->where('internal_name', 'like', "%{$search}%")
                        ->orderBy('internal_name')
                        ->limit(50)
                        ->pluck('internal_name', 'id')
                        ->all()
                    )
                    ->getOptionLabelUsing(fn ($v): string => Item::find($v)?->internal_name ?? $v),
                Select::make('language_id')
                    ->label('Language')
                    ->relationship('language', 'internal_name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->unique(
                        table: 'item_translations',
                        column: 'language_id',
                        modifyRuleUsing: fn (Unique $rule, Get $get, ?ItemTranslation $record): Unique => $rule
                            ->where('item_id', $get('item_id') ?? '')
                            ->where('context_id', $get('context_id') ?? '')
                            ->ignore($record?->id),
                        ignoreRecord: true,
                    )
                    ->validationMessages(['unique' => 'A translation for this item, language and context already exists.']),
                Select::make('context_id')
                    ->label('Context')
                    ->relationship('context', 'internal_name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Section::make('Basic Text')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('alternate_name')
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Object Details')
                    ->schema([
                        TextInput::make('type')
                            ->placeholder('e.g., painting, sculpture, monument')
                            ->maxLength(255),
                        TextInput::make('dates')
                            ->placeholder('e.g., 1920–1930, 18th century')
                            ->maxLength(255),
                        TextInput::make('location')
                            ->placeholder('Current or original location')
                            ->maxLength(255),
                        TextInput::make('dimensions')
                            ->placeholder('e.g., 100×80 cm, 2 m height')
                            ->maxLength(255),
                        TextInput::make('place_of_production')
                            ->label('Place of production')
                            ->placeholder('Where the item was created')
                            ->maxLength(255),
                        Textarea::make('holder')
                            ->placeholder('Current holder of the item')
                            ->rows(2),
                        Textarea::make('owner')
                            ->placeholder('Current owner of the item')
                            ->rows(2),
                        Textarea::make('initial_owner')
                            ->label('Initial owner')
                            ->placeholder('Original owner of the item')
                            ->rows(2),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

                Section::make('Research & Provenance')
                    ->schema([
                        Textarea::make('method_for_datation')
                            ->label('Method for datation')
                            ->placeholder('Method used to date the item')
                            ->rows(3),
                        Textarea::make('method_for_provenance')
                            ->label('Method for provenance')
                            ->placeholder('Method used to determine provenance')
                            ->rows(3),
                        Textarea::make('provenance')
                            ->placeholder('Provenance details')
                            ->rows(3),
                        Textarea::make('obtention')
                            ->placeholder('How the item was obtained')
                            ->rows(3),
                        Textarea::make('bibliography')
                            ->placeholder('Bibliographic references')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

                Section::make('Contributors')
                    ->schema([
                        TranslationFormSchema::authorSelectField('author_id', 'Author'),
                        TranslationFormSchema::authorSelectField('text_copy_editor_id', 'Text copy editor'),
                        TranslationFormSchema::authorSelectField('translator_id', 'Translator'),
                        TranslationFormSchema::authorSelectField('translation_copy_editor_id', 'Translation copy editor'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

                Section::make('Legacy & Metadata')
                    ->schema([
                        TranslationFormSchema::backwardCompatibilityField(),
                        TranslationFormSchema::extraField(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'item:id,internal_name',
                'language:id,internal_name,is_default',
                'context:id,internal_name,is_default',
            ]))
            ->columns([
                TextColumn::make('item.internal_name')
                    ->label('Item')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('language.internal_name')
                    ->label('Language')
                    ->sortable()
                    ->badge()
                    ->color(fn (ItemTranslation $r): string => $r->language?->is_default ? 'success' : 'gray'),
                TextColumn::make('context.internal_name')
                    ->label('Context')
                    ->sortable()
                    ->badge()
                    ->color(fn (ItemTranslation $r): string => $r->context?->is_default ? 'success' : 'gray'),
                IconColumn::make('is_default_pair')
                    ->label('★')
                    ->tooltip('Default language + context pair')
                    ->getStateUsing(fn (ItemTranslation $r): bool => (bool) ($r->language?->is_default && $r->context?->is_default))
                    ->trueIcon('heroicon-s-star')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                TextColumn::make('name')
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
                    ->query(fn (Builder $query): Builder => $query->whereHas('item', fn (Builder $iq): Builder => $iq->whereDoesntHave('translations', fn (Builder $tq): Builder => $tq
                        ->whereHas('language', fn (Builder $lq): Builder => $lq->where('is_default', true))
                        ->whereHas('context', fn (Builder $cq): Builder => $cq->where('is_default', true))
                    ))),
                Filter::make('recently_updated')
                    ->label('Recently updated (30 days)')
                    ->query(fn (Builder $query): Builder => $query->where('updated_at', '>=', now()->subDays(30))),
            ])
            ->actions([
                Action::make('viewItem')
                    ->label('View item')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (ItemTranslation $r): string => ItemResource::getUrl('view', ['record' => $r->item_id]))
                    ->openUrlInNewTab(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListItemTranslation::route('/'),
            'create' => CreateItemTranslation::route('/create'),
            'edit' => EditItemTranslation::route('/{record}/edit'),
        ];
    }
}

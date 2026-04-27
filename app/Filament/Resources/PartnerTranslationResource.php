<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\HasTimestampsColumns;
use App\Filament\Resources\PartnerTranslationResource\Pages\CreatePartnerTranslation;
use App\Filament\Resources\PartnerTranslationResource\Pages\EditPartnerTranslation;
use App\Filament\Resources\PartnerTranslationResource\Pages\ListPartnerTranslation;
use App\Filament\Resources\PartnerTranslationResource\RelationManagers\ImagesRelationManager;
use App\Filament\Support\TranslationFormSchema;
use App\Models\Partner;
use App\Models\PartnerTranslation;
use Filament\Forms\Components\Repeater;
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

class PartnerTranslationResource extends Resource
{
    use HasTimestampsColumns;

    protected static ?string $model = PartnerTranslation::class;

    protected static ?string $navigationIcon = 'heroicon-o-language';

    protected static ?string $navigationGroup = 'Translations';

    protected static ?string $navigationLabel = 'Partner Translations';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('partner_id')
                    ->label('Partner')
                    ->required()
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => Partner::query()
                        ->where('internal_name', 'like', "%{$search}%")
                        ->orderBy('internal_name')
                        ->limit(50)
                        ->pluck('internal_name', 'id')
                        ->all()
                    )
                    ->getOptionLabelUsing(fn ($v): string => Partner::find($v)?->internal_name ?? $v),
                Select::make('language_id')
                    ->label('Language')
                    ->relationship('language', 'internal_name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->unique(
                        table: 'partner_translations',
                        column: 'language_id',
                        modifyRuleUsing: fn (Unique $rule, Get $get, ?PartnerTranslation $record): Unique => $rule
                            ->where('partner_id', $get('partner_id') ?? '')
                            ->where('context_id', $get('context_id') ?? '')
                            ->ignore($record?->id),
                        ignoreRecord: true,
                    )
                    ->validationMessages(['unique' => 'A translation for this partner, language and context already exists.']),
                Select::make('context_id')
                    ->label('Context')
                    ->relationship('context', 'internal_name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('city_display')
                            ->label('City (display)')
                            ->placeholder('City name to display (may differ from actual address)')
                            ->maxLength(255),
                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Address')
                    ->schema([
                        TextInput::make('address_line_1')
                            ->label('Address line 1')
                            ->placeholder('Street address')
                            ->maxLength(255),
                        TextInput::make('address_line_2')
                            ->label('Address line 2')
                            ->placeholder('Apartment, suite, etc.')
                            ->maxLength(255),
                        TextInput::make('postal_code')
                            ->label('Postal code')
                            ->placeholder('ZIP / Postal code')
                            ->maxLength(255),
                        Textarea::make('address_notes')
                            ->label('Address notes')
                            ->placeholder('Additional address information or directions')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

                Section::make('Contact Information')
                    ->schema([
                        TextInput::make('contact_name')
                            ->label('Contact name')
                            ->placeholder('Primary contact person')
                            ->maxLength(255),
                        TextInput::make('contact_phone')
                            ->label('Phone')
                            ->placeholder('+1 234 567 8900')
                            ->maxLength(255),
                        TextInput::make('contact_email_general')
                            ->label('General email')
                            ->email()
                            ->placeholder('general@example.com')
                            ->maxLength(255),
                        TextInput::make('contact_email_press')
                            ->label('Press email')
                            ->email()
                            ->placeholder('press@example.com')
                            ->maxLength(255),
                        TextInput::make('contact_website')
                            ->label('Website')
                            ->url()
                            ->placeholder('https://example.com')
                            ->maxLength(255),
                        Textarea::make('contact_notes')
                            ->label('Contact notes')
                            ->placeholder('Additional contact information')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

                Section::make('Additional Contacts')
                    ->schema([
                        Repeater::make('contact_emails')
                            ->label('Additional email addresses')
                            ->simple(
                                TextInput::make('value')
                                    ->label('Email')
                                    ->email()
                            )
                            ->columnSpanFull(),
                        Repeater::make('contact_phones')
                            ->label('Additional phone numbers')
                            ->simple(
                                TextInput::make('value')
                                    ->label('Phone')
                                    ->tel()
                            )
                            ->columnSpanFull(),
                    ])
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
                'partner:id,internal_name',
                'language:id,internal_name,is_default',
                'context:id,internal_name,is_default',
            ]))
            ->columns([
                TextColumn::make('partner.internal_name')
                    ->label('Partner')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('language.internal_name')
                    ->label('Language')
                    ->sortable()
                    ->badge()
                    ->color(fn (PartnerTranslation $r): string => $r->language?->is_default ? 'success' : 'gray'),
                TextColumn::make('context.internal_name')
                    ->label('Context')
                    ->sortable()
                    ->badge()
                    ->color(fn (PartnerTranslation $r): string => $r->context?->is_default ? 'success' : 'gray'),
                IconColumn::make('is_default_pair')
                    ->label('★')
                    ->tooltip('Default language + context pair')
                    ->getStateUsing(fn (PartnerTranslation $r): bool => (bool) ($r->language?->is_default && $r->context?->is_default))
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
                    ->query(fn (Builder $query): Builder => $query->whereHas('partner', fn (Builder $pq): Builder => $pq->whereDoesntHave('translations', fn (Builder $tq): Builder => $tq
                        ->whereHas('language', fn (Builder $lq): Builder => $lq->where('is_default', true))
                        ->whereHas('context', fn (Builder $dq): Builder => $dq->where('is_default', true))
                    ))),
                Filter::make('recently_updated')
                    ->label('Recently updated (30 days)')
                    ->query(fn (Builder $query): Builder => $query->where('updated_at', '>=', now()->subDays(30))),
            ])
            ->actions([
                Action::make('viewPartner')
                    ->label('View partner')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (PartnerTranslation $r): string => PartnerResource::getUrl('view', ['record' => $r->partner_id]))
                    ->openUrlInNewTab(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ImagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPartnerTranslation::route('/'),
            'create' => CreatePartnerTranslation::route('/create'),
            'edit' => EditPartnerTranslation::route('/{record}/edit'),
        ];
    }
}

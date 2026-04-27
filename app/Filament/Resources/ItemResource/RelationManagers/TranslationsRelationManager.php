<?php

namespace App\Filament\Resources\ItemResource\RelationManagers;

use App\Filament\Resources\ItemResource;
use App\Filament\Support\TranslationFormSchema;
use App\Models\Context;
use App\Models\ItemTranslation;
use App\Models\Language;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;

class TranslationsRelationManager extends RelationManager
{
    protected static string $relationship = 'translations';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $title = 'Translations';

    public function form(Form $form): Form
    {
        $ownerRecord = $this->ownerRecord;

        return $form
            ->schema([
                TranslationFormSchema::languageField()
                    ->unique(
                        table: 'item_translations',
                        column: 'language_id',
                        modifyRuleUsing: function (Unique $rule, Get $get) use ($ownerRecord): Unique {
                            return $rule
                                ->where('item_id', $ownerRecord->id)
                                ->where('context_id', $get('context_id') ?? '');
                        },
                        ignoreRecord: true,
                    )
                    ->validationMessages(['unique' => 'A translation for this language and context already exists.']),
                TranslationFormSchema::contextField(),
                TranslationFormSchema::nameField(),
                TranslationFormSchema::alternateNameField(),
                TranslationFormSchema::descriptionField(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'context:id,internal_name,is_default',
                'language:id,internal_name,is_default',
            ]))
            ->defaultSort('name', 'asc')
            ->paginated([25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
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
                TextColumn::make('alternate_name')
                    ->label('Alternate name')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ])
            ->headerActions([
                CreateAction::make()
                    ->fillForm(fn (): array => [
                        'language_id' => Language::default()->first()?->id,
                        'context_id' => Context::default()->first()?->id,
                    ]),
                Action::make('createDefaultTranslation')
                    ->label('Create default translation')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->form([
                        TranslationFormSchema::languageField(),
                        TranslationFormSchema::contextField(),
                        TranslationFormSchema::nameField(),
                        TranslationFormSchema::alternateNameField(),
                        TranslationFormSchema::descriptionField(),
                    ])
                    ->fillForm(fn (): array => [
                        'language_id' => Language::default()->first()?->id,
                        'context_id' => Context::default()->first()?->id,
                    ])
                    ->visible(fn (): bool => ! $this->ownerRecord->translations()
                        ->whereHas('language', fn ($q) => $q->where('is_default', true))
                        ->whereHas('context', fn ($q) => $q->where('is_default', true))
                        ->exists()
                    )
                    ->action(function (array $data): void {
                        $exists = $this->ownerRecord->translations()
                            ->where('language_id', $data['language_id'])
                            ->where('context_id', $data['context_id'])
                            ->exists();

                        if ($exists) {
                            Notification::make()
                                ->warning()
                                ->title('Translation already exists for this language and context.')
                                ->send();

                            return;
                        }

                        $this->ownerRecord->translations()->create($data);

                        Notification::make()
                            ->success()
                            ->title('Default translation created.')
                            ->send();
                    }),
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
}

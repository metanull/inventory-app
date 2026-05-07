<?php

namespace App\Filament\Resources\ItemTranslationResource\RelationManagers;

use App\Filament\Resources\ContextResource;
use App\Filament\Resources\ItemTranslationResource;
use App\Filament\Resources\LanguageResource;
use App\Models\ItemTranslation;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SiblingTranslationsRelationManager extends RelationManager
{
    protected static string $relationship = 'siblingTranslations';

    protected static ?string $title = 'Sibling Translations';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->with(['language:id,internal_name,is_default', 'context:id,internal_name,is_default'])
                ->where('id', '!=', $this->ownerRecord->id)
                ->orderBy('updated_at', 'desc')
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('language.internal_name')
                    ->label('Language')
                    ->badge()
                    ->color(fn (ItemTranslation $r): string => $r->language?->is_default ? 'success' : 'gray')
                    ->url(fn (ItemTranslation $r): ?string => $r->language
                        ? (auth()->user()?->can('view', $r->language) ? LanguageResource::getUrl('view', ['record' => $r->language]) : null)
                        : null),
                TextColumn::make('context.internal_name')
                    ->label('Context')
                    ->badge()
                    ->color(fn (ItemTranslation $r): string => $r->context?->is_default ? 'success' : 'gray')
                    ->url(fn (ItemTranslation $r): ?string => $r->context
                        ? (auth()->user()?->can('view', $r->context) ? ContextResource::getUrl('view', ['record' => $r->context]) : null)
                        : null),
                IconColumn::make('is_default_pair')
                    ->label('★')
                    ->tooltip('Default language + context pair')
                    ->getStateUsing(fn (ItemTranslation $r): bool => (bool) ($r->language?->is_default && $r->context?->is_default))
                    ->trueIcon('heroicon-s-star')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->falseColor('gray'),
                TextColumn::make('name')
                    ->label('Name')
                    ->limit(50),
                TextColumn::make('backward_compatibility')
                    ->label('Legacy ID')
                    ->placeholder('—'),
                TextColumn::make('id')
                    ->label('UUID')
                    ->limit(8)
                    ->tooltip(fn (ItemTranslation $r): string => $r->id)
                    ->fontFamily('mono'),
            ])
            ->actions([
                Action::make('viewTranslation')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn (ItemTranslation $r): string => ItemTranslationResource::getUrl('view', ['record' => $r])),
                Action::make('editTranslation')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->url(fn (ItemTranslation $r): string => ItemTranslationResource::getUrl('edit', ['record' => $r]))
                    ->visible(fn (ItemTranslation $r): bool => auth()->user()?->can('update', $r) ?? false),
            ]);
    }
}

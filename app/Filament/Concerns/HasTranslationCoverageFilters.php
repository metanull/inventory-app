<?php

namespace App\Filament\Concerns;

use App\Models\Context;
use App\Models\Language;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

trait HasTranslationCoverageFilters
{
    /**
     * Returns an IconColumn that indicates whether the record has the default
     * fallback translation (default language + default context pair).
     *
     * The owning resource's modifyQueryUsing must call withFallbackExists() to
     * populate the virtual has_fallback_translation attribute.
     */
    protected static function fallbackTranslationColumn(): IconColumn
    {
        return IconColumn::make('has_fallback_translation')
            ->label('Fallback')
            ->tooltip('Has default language + context translation')
            ->boolean()
            ->trueIcon('heroicon-s-check-circle')
            ->falseIcon('heroicon-o-x-circle')
            ->trueColor('success')
            ->falseColor('danger')
            ->sortable(false);
    }

    /**
     * Adds a withExists() sub-query to the table query that populates the
     * has_fallback_translation virtual boolean attribute on each result row.
     */
    protected static function withFallbackExists(Builder $query): Builder
    {
        return $query->withExists([
            'translations as has_fallback_translation' => fn (Builder $q): Builder => $q
                ->where('language_id', Language::default()->value('id'))
                ->where('context_id', Context::default()->value('id')),
        ]);
    }

    /**
     * Returns the shared translation coverage filter set.
     *
     * All closures use $query as the outer parameter name to satisfy Filament's
     * named-injection resolver (see HasTranslationCoverageFilters memory note).
     */
    protected static function translationCoverageFilters(): array
    {
        return [
            Filter::make('has_fallback_translation')
                ->label('Has fallback translation')
                ->query(fn (Builder $query): Builder => $query->whereHas(
                    'translations',
                    fn (Builder $q): Builder => $q
                        ->whereHas('language', fn (Builder $ql): Builder => $ql->where('is_default', true))
                        ->whereHas('context', fn (Builder $qc): Builder => $qc->where('is_default', true))
                )),

            Filter::make('missing_fallback_translation')
                ->label('Missing fallback translation')
                ->query(fn (Builder $query): Builder => $query->whereDoesntHave(
                    'translations',
                    fn (Builder $q): Builder => $q
                        ->whereHas('language', fn (Builder $ql): Builder => $ql->where('is_default', true))
                        ->whereHas('context', fn (Builder $qc): Builder => $qc->where('is_default', true))
                )),

            SelectFilter::make('translation_language_has')
                ->label('Has translation in language')
                ->options(fn (): array => Language::query()
                    ->orderBy('internal_name')
                    ->pluck('internal_name', 'id')
                    ->all())
                ->query(fn (Builder $query, array $data): Builder => $data['value']
                    ? $query->whereHas('translations', fn (Builder $q): Builder => $q->where('language_id', $data['value']))
                    : $query),

            SelectFilter::make('translation_language_missing')
                ->label('Missing translation in language')
                ->options(fn (): array => Language::query()
                    ->orderBy('internal_name')
                    ->pluck('internal_name', 'id')
                    ->all())
                ->query(fn (Builder $query, array $data): Builder => $data['value']
                    ? $query->whereDoesntHave('translations', fn (Builder $q): Builder => $q->where('language_id', $data['value']))
                    : $query),

            SelectFilter::make('translation_context_has')
                ->label('Has translation in context')
                ->options(fn (): array => Context::query()
                    ->orderBy('internal_name')
                    ->pluck('internal_name', 'id')
                    ->all())
                ->query(fn (Builder $query, array $data): Builder => $data['value']
                    ? $query->whereHas('translations', fn (Builder $q): Builder => $q->where('context_id', $data['value']))
                    : $query),

            SelectFilter::make('translation_context_missing')
                ->label('Missing translation in context')
                ->options(fn (): array => Context::query()
                    ->orderBy('internal_name')
                    ->pluck('internal_name', 'id')
                    ->all())
                ->query(fn (Builder $query, array $data): Builder => $data['value']
                    ? $query->whereDoesntHave('translations', fn (Builder $q): Builder => $q->where('context_id', $data['value']))
                    : $query),
        ];
    }
}

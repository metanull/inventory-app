<?php

namespace App\Filament\Support;

use App\Models\Context;
use App\Models\Item;
use App\Models\Language;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

/**
 * Resolves a user-facing display label for Item records in Filament.
 *
 * The fallback chain, stopping at the first non-empty value:
 *  1. Default language + collection's context_id (when collection_id is present).
 *  2. Default language + project's context_id (when project_id is present).
 *  3. Default language + the default context.
 *  4. First translation in the default language (any context).
 *  5. First translation in any language.
 *  6. items.internal_name.
 */
class ItemDisplayLabel
{
    /**
     * Append a COALESCE correlated sub-query that resolves to `display_label`
     * on every row of an Item query.
     *
     * Call this inside modifyQueryUsing() or any other query-building hook.
     * The helper ensures `items.*` is preserved in the column list when
     * no explicit SELECT has been issued yet.
     */
    public static function withDisplayLabel(Builder $query): Builder
    {
        $defaultLangId = Language::default()->value('id') ?? '';
        $defaultContextId = Context::default()->value('id') ?? '';

        if (is_null($query->getQuery()->columns)) {
            $query->getQuery()->columns = ['items.*'];
        }

        return $query->selectRaw(
            "COALESCE(
                (SELECT it1a.name FROM item_translations it1a
                 WHERE it1a.item_id = items.id
                   AND it1a.language_id = ?
                   AND it1a.context_id = (SELECT c.context_id FROM collections c WHERE c.id = items.collection_id LIMIT 1)
                   AND it1a.name IS NOT NULL AND it1a.name != ''
                 LIMIT 1),
                (SELECT it1b.name FROM item_translations it1b
                 WHERE it1b.item_id = items.id
                   AND it1b.language_id = ?
                   AND it1b.context_id = (SELECT p.context_id FROM projects p WHERE p.id = items.project_id LIMIT 1)
                   AND it1b.name IS NOT NULL AND it1b.name != ''
                 LIMIT 1),
                (SELECT it2.name FROM item_translations it2
                 WHERE it2.item_id = items.id
                   AND it2.language_id = ?
                   AND it2.context_id = ?
                   AND it2.name IS NOT NULL AND it2.name != ''
                 LIMIT 1),
                (SELECT it3.name FROM item_translations it3
                 WHERE it3.item_id = items.id
                   AND it3.language_id = ?
                   AND it3.name IS NOT NULL AND it3.name != ''
                 LIMIT 1),
                (SELECT it4.name FROM item_translations it4
                 WHERE it4.item_id = items.id
                   AND it4.name IS NOT NULL AND it4.name != ''
                 LIMIT 1),
                items.internal_name
            ) AS display_label",
            [$defaultLangId, $defaultLangId, $defaultLangId, $defaultContextId, $defaultLangId]
        );
    }

    /**
     * Resolve the display label for a single Item record via PHP.
     *
     * Translations must already be loaded (or will be lazy-loaded on demand).
     * The collection and project relationships must be loaded (or will be
     * lazy-loaded) to resolve the own-context step.
     *
     * @param  Item  $item  The item whose label to resolve.
     * @return string The resolved display label.
     */
    public static function resolveForRecord(Item $item): string
    {
        $defaultLangId = Language::default()->value('id');
        $defaultContextId = Context::default()->value('id');

        $translations = $item->translations;

        // Resolve own context: try collection first, then project
        $ownContextId = null;
        if ($item->collection_id && $item->collection) {
            $ownContextId = $item->collection->context_id;
        }
        if (! $ownContextId && $item->project_id && $item->project) {
            $ownContextId = $item->project->context_id;
        }

        // 1. Default language + own context (collection or project)
        if ($ownContextId && $defaultLangId) {
            $t = $translations->first(
                fn ($t) => $t->language_id === $defaultLangId
                    && $t->context_id === $ownContextId
                    && ! empty($t->name)
            );
            if ($t) {
                return $t->name;
            }
        }

        // 2. Default language + default context
        if ($defaultLangId && $defaultContextId) {
            $t = $translations->first(
                fn ($t) => $t->language_id === $defaultLangId
                    && $t->context_id === $defaultContextId
                    && ! empty($t->name)
            );
            if ($t) {
                return $t->name;
            }
        }

        // 3. First translation in default language (any context)
        if ($defaultLangId) {
            $t = $translations->first(
                fn ($t) => $t->language_id === $defaultLangId && ! empty($t->name)
            );
            if ($t) {
                return $t->name;
            }
        }

        // 4. First translation in any language
        $t = $translations->first(fn ($t) => ! empty($t->name));
        if ($t) {
            return $t->name;
        }

        // 5. Fallback
        return $item->internal_name;
    }

    /**
     * Resolve the display label for an item identified by its primary key.
     *
     * Loads the record and its translations in two queries. Suitable for
     * getOptionLabelUsing() callbacks where only a single value is needed.
     *
     * @param  mixed  $value  The item UUID.
     * @return string The resolved display label, or the raw value if not found.
     */
    public static function resolveLabel(mixed $value): string
    {
        if (! $value) {
            return (string) $value;
        }

        $item = Item::find($value);
        if (! $item) {
            return (string) $value;
        }

        $item->load(['translations', 'collection:id,context_id', 'project:id,context_id']);

        return static::resolveForRecord($item);
    }

    /**
     * Return a Filament TextColumn that shows the resolved display label.
     *
     * The column is deliberately not sortable/searchable because it maps to
     * a SQL COALESCE virtual column. Keep `internal_name` searchable alongside
     * this column for technical look-ups.
     */
    public static function displayLabelColumn(): TextColumn
    {
        return TextColumn::make('display_label')
            ->label('Name')
            ->searchable(false)
            ->sortable(false);
    }
}

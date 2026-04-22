---
layout: default
title: Searchable Multi-Select
nav_order: 2
parent: Livewire
grand_parent: Blade/Livewire Frontend
---

# Searchable Multi-Select

The `SearchableMultiSelect` Livewire component is the multi-value counterpart of `SearchableSelect`. It renders a chip for each selected option and a live-search combobox for picking additional candidates.

## When to Use

| Scenario                                                            | Component               |
| ------------------------------------------------------------------- | ----------------------- |
| Single-value relationship picker (parent item, default language, …) | `SearchableSelect`      |
| Multi-value filter on an index page (tags, countries, …)            | `SearchableMultiSelect` |
| Multi-value relationship (item links, gallery images, …)            | `SearchableMultiSelect` |

Use `SearchableMultiSelect` whenever the user selects **zero or more** values from a potentially large list.

## How It Works

- **Chip rendering** — selected options appear as dismissable chips above the search input.
- **Live search** — candidates are fetched server-side as the user types, using prefix-first ordering.
- **Scope composition** — the same named-scope support as `SearchableSelect` lets you narrow candidates (e.g., show only enabled projects, or tags of a specific category).
- **Hidden inputs** — the component emits one `name[]` hidden input per selected id so that standard HTML form submission round-trips through the existing `IndexListRequest` filter resolution without any changes.
- **No Eloquent in state** — selected ids are stored as a plain string array. The `selectedOptions` collection is resolved on-demand from the selected ids; no Eloquent model instances are held in the Livewire snapshot.

## Blade Wrapper Props

| Prop                | Type                  | Default               | Description                                                                                                                                                                    |
| ------------------- | --------------------- | --------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `name`              | `string`              | `''`                  | Field name; controls the `name[]` hidden inputs.                                                                                                                               |
| `label`             | `string`              | `''`                  | Optional visible label rendered above the component.                                                                                                                           |
| `selectedOptions`   | `Collection\|null`    | `null`                | **Caller responsibility** — pre-load only the currently selected rows from the database (bounded by selection count). Used to derive `selectedIds` for initial chip rendering. |
| `displayField`      | `string`              | `internal_name`       | Model attribute to display in chips and the dropdown.                                                                                                                          |
| `placeholder`       | `string`              | `'Select...'`         | Placeholder shown when no options are selected.                                                                                                                                |
| `searchPlaceholder` | `string`              | `'Type to search...'` | Placeholder inside the search input.                                                                                                                                           |
| `entity`            | `string\|null`        | `null`                | Entity color key (e.g., `'items'`, `'tags'`) for Tailwind focus ring theming.                                                                                                  |
| `modelClass`        | `string\|null`        | `null`                | Fully-qualified model class for dynamic mode.                                                                                                                                  |
| `scopes`            | `string\|array\|null` | `null`                | Named Eloquent scope(s) to narrow candidates (see [SearchableSelect scopes](/frontend-blade/livewire/searchable-select)).                                                      |
| `perPage`           | `int\|null`           | `null`                | Max candidates per search request (defaults to `interface.searchable_select.per_page`).                                                                                        |
| `options`           | `array\|null`         | `null`                | Static options array for small bounded enums. Do **not** use for growable entities.                                                                                            |
| `filterColumn`      | `string\|null`        | `null`                | Optional column to apply an additional WHERE filter.                                                                                                                           |
| `filterOperator`    | `string`              | `'!='`                | Operator for the filter column (`!=`, `IN`, `NOT IN`, etc.).                                                                                                                   |
| `filterValue`       | `mixed`               | `null`                | Value for the filter column.                                                                                                                                                   |

## `selectedOptions` Contract

The `selectedOptions` prop is a pre-loaded collection supplied by the **caller** (controller or Blade view). It is used only to derive the initial set of selected ids and is never serialized into the Livewire component state. After mount, the component resolves chip labels itself by querying `whereIn('id', $selectedIds)`.

The caller is responsible for keeping `selectedOptions` bounded: query only the rows whose ids are in the current filter/form value. For an index-page tag filter with three selected tags, load those three tag models — not the entire tags table.

## Canonical Usage: Filter Bar (Tags on Items Index)

```blade
{{-- In the filter bar partial of items/index.blade.php --}}
<x-form.searchable-multi-select
    name="filter[tag_ids]"
    label="Tags"
    :modelClass="\App\Models\Tag::class"
    displayField="internal_name"
    entity="tags"
    :selectedOptions="$listState->filterValue('tag_ids')
        ? \App\Models\Tag::whereIn('id', $listState->filterValue('tag_ids'))->get()
        : collect()"
    placeholder="Filter by tag..."
/>
```

The hidden inputs submitted will be `filter[tag_ids][]`, which `IndexListRequest` resolves to an array of ids — no changes to the request pipeline required.

## Canonical Usage: Form Multi-Picker

```blade
{{-- In a _form.blade.php where $item->tags is the current relationship --}}
<x-form.searchable-multi-select
    name="tag_ids"
    label="Tags"
    :modelClass="\App\Models\Tag::class"
    displayField="internal_name"
    entity="tags"
    :selectedOptions="$item->tags"
    placeholder="Add a tag..."
/>
```

The controller reads `$request->input('tag_ids', [])` and passes it to `$item->tags()->sync(...)`.

## Shared Query Engine

`SearchableMultiSelect` delegates all query composition to the `App\Livewire\Support\OptionsLookup` trait, the same trait used by `SearchableSelect`. This ensures prefix-first ordering, named-scope support, filter-column application, and the `perPage` ceiling are identical between the two components.

## Notes

- Duplicate `addOption` calls for the same id are silently ignored.
- Already-selected ids are excluded from the candidate dropdown so the user cannot select the same option twice.
- `clear()` resets all selected ids and the search field in a single action.

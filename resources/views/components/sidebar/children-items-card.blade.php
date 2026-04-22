{{--
    Sidebar Card for Children Items
    Uses unified item-relationship-card component
--}}

@props(['model', 'children', 'collection' => null])

@php
    $childItems = $children->map(fn ($child) => (object) [
        'record' => $child,
        'removeAction' => route('items.removeChild', [$model, $child]),
    ]);
@endphp

<x-sidebar.item-relationship-card
    title="Children"
    :model="$model"
    :items="$childItems"
    type="children"
    :add-route="route('items.addChild', $model)"
    :can-add="true"
    :can-remove="true"
    :count="$children->count()"
    :collection="$collection"
>
    <x-form.entity-select
        name="child_id"
        :value="null"
        model-class="\App\Models\Item"
        display-field="internal_name"
        value-field="id"
        filter-column="parent_id"
        filter-operator="!="
        :filter-value="$model->id"
        placeholder="Search items..."
        search-placeholder="Type name or ID..."
        required
        entity="items"
    />
</x-sidebar.item-relationship-card>


{{--
    Sidebar Card for Children Items
    Uses unified item-relationship-card component
--}}

@props(['model', 'children', 'childOptions', 'collection' => null])

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
        :options="$childOptions"
        display-field="internal_name"
        value-field="id"
        placeholder="Search items..."
        search-placeholder="Type name or ID..."
        required
        entity="items"
    />
</x-sidebar.item-relationship-card>


{{--
    Sidebar Card for Parent Item
    Uses unified item-relationship-card component
--}}

@props(['model', 'parentItem' => null, 'collection' => null])

<x-sidebar.item-relationship-card
    title="Parent Item"
    :model="$model"
    :items="$parentItem ? collect([$parentItem]) : collect()"
    type="parent"
    :add-route="route('items.setParent', $model)"
    :remove-route="route('items.removeParent', $model)"
    :can-add="true"
    :can-remove="true"
    :collection="$collection"
>
    <x-form.entity-select
        name="parent_id"
        :value="$model->parent_id"
        model-class="\App\Models\Item"
        display-field="internal_name"
        value-field="id"
        filter-column="id"
        filter-operator="!="
        :filter-value="$model->id"
        placeholder="Search items..."
        search-placeholder="Type name or ID..."
        required
        entity="items"
    />
</x-sidebar.item-relationship-card>


{{--
    Sidebar Card for Parent Item
    Uses unified item-relationship-card component
--}}

@props(['model'])

<x-sidebar.item-relationship-card
    title="Parent Item"
    :model="$model"
    :items="$model->parent ? collect([$model->parent]) : collect()"
    type="parent"
    :add-route="route('items.setParent', $model)"
    :remove-route="route('items.removeParent', $model)"
    :can-add="true"
    :can-remove="true"
>
    <x-form.entity-select 
        name="parent_id" 
        :value="$model->parent_id"
        :model-class="\App\Models\Item::class"
        display-field="internal_name"
        value-field="id"
        placeholder="Search items..."
        search-placeholder="Type name or ID..."
        required
        entity="items"
        :filter-column="'id'"
        :filter-operator="'!='"
        :filter-value="$model->id"
    />
</x-sidebar.item-relationship-card>


{{--
    Sidebar Card for Children Items
    Uses unified item-relationship-card component
--}}

@props(['model'])

<x-sidebar.item-relationship-card
    title="Children"
    :model="$model"
    :items="$model->children"
    type="children"
    :add-route="route('items.addChild', $model)"
    :remove-route="route('items.removeChild', [$model, ':id'])"
    :can-add="true"
    :can-remove="true"
    :count="$model->children->count()"
>
    <x-form.entity-select 
        name="child_id" 
        :value="null"
        :model-class="\App\Models\Item::class"
        display-field="internal_name"
        value-field="id"
        placeholder="Search items..."
        search-placeholder="Type name or ID..."
        required
        entity="items"
        :filter-column="'id'"
        :filter-operator="'NOT IN'"
        :filter-value="array_merge([$model->id], $model->children->pluck('id')->toArray())"
    />
</x-sidebar.item-relationship-card>


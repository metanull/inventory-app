{{--
    Sidebar Card for Parent Collection
    Uses unified collection-relationship-card component
--}}

@props(['model'])

<x-sidebar.collection-relationship-card
    title="Parent Collection"
    :model="$model"
    :collections="$model->parent ? collect([$model->parent]) : collect()"
    type="parent"
    :add-route="route('collections.setParent', $model)"
    :remove-route="route('collections.removeParent', $model)"
    :can-add="true"
    :can-remove="true"
>
    <x-form.entity-select
        name="parent_id"
        :value="$model->parent_id"
        :model-class="\App\Models\Collection::class"
        display-field="internal_name"
        value-field="id"
        placeholder="Search collections..."
        search-placeholder="Type name or ID..."
        required
        entity="collections"
        :filter-column="'id'"
        :filter-operator="'!='"
        :filter-value="$model->id"
    />
</x-sidebar.collection-relationship-card>

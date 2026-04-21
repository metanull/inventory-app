{{--
    Sidebar Card for Parent Collection
    Uses unified collection-relationship-card component
--}}

@props(['model', 'parentCollection' => null, 'parentOptions'])

<x-sidebar.collection-relationship-card
    title="Parent Collection"
    :model="$model"
    :collections="$parentCollection ? collect([$parentCollection]) : collect()"
    type="parent"
    :add-route="route('collections.setParent', $model)"
    :remove-route="route('collections.removeParent', $model)"
    :can-add="true"
    :can-remove="true"
>
    <x-form.entity-select
        name="parent_id"
        :value="$model->parent_id"
        :options="$parentOptions"
        display-field="internal_name"
        value-field="id"
        placeholder="Search collections..."
        search-placeholder="Type name or ID..."
        required
        entity="collections"
    />
</x-sidebar.collection-relationship-card>

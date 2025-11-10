{{--
    Sidebar Card for Partner's Monument Item
    Uses unified item-relationship-card component
--}}

@props(['partner'])

<x-sidebar.item-relationship-card
    title="Monument Item"
    :model="$partner"
    :items="$partner->monumentItem ? collect([$partner->monumentItem]) : collect()"
    type="monument"
    :add-route="route('partners.setMonument', $partner)"
    :remove-route="route('partners.removeMonument', $partner)"
    :can-add="true"
    :can-remove="true"
>
    <x-form.entity-select 
        name="monument_item_id" 
        :value="$partner->monument_item_id"
        :model-class="\App\Models\Item::class"
        display-field="internal_name"
        value-field="id"
        placeholder="Search monument items..."
        search-placeholder="Type name or ID..."
        required
        entity="items"
        :filter-column="'type'"
        :filter-operator="'='"
        :filter-value="'monument'"
    />
</x-sidebar.item-relationship-card>

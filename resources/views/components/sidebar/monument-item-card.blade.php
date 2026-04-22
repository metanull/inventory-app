{{--
    Sidebar Card for Partner's Monument Item
    Uses unified item-relationship-card component
--}}

@props(['partner', 'monumentItem' => null])

<x-sidebar.item-relationship-card
    title="Monument Item"
    :model="$partner"
    :items="$monumentItem ? collect([$monumentItem]) : collect()"
    type="monument"
    :add-route="route('partners.setMonument', $partner)"
    :remove-route="route('partners.removeMonument', $partner)"
    :can-add="true"
    :can-remove="true"
>
    <x-form.entity-select 
        name="monument_item_id" 
        :value="$partner->monument_item_id"
        model-class="\App\Models\Item"
        display-field="internal_name"
        value-field="id"
        :scopes="[['scope' => 'monuments']]"
        placeholder="Search monument items..."
        search-placeholder="Type name or ID..."
        required
        entity="items"
    />
</x-sidebar.item-relationship-card>

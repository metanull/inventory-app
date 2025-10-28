@csrf

<div class="p-6 space-y-6">
    <x-form.field label="Internal Name" name="internal_name" variant="gray" required>
    <x-form.input 
        name="internal_name" 
        :value="old('internal_name', $item->internal_name ?? '')" 
        required 
    />
</x-form.field>

<x-form.field label="Type" name="type" required>
    <x-form.entity-select 
        name="type" 
        :value="old('type', $item->type ?? '')"
        :options="collect([
            (object)['id' => 'object', 'name' => 'Object'],
            (object)['id' => 'monument', 'name' => 'Monument']
        ])"
        displayField="name"
        valueField="id"
        placeholder="Select type..."
        searchPlaceholder="Type to search..."
        entity="items"
        required
    />
</x-form.field>

<x-form.field label="Country" name="country_id" variant="gray">
    <x-form.entity-select 
        name="country_id" 
        :value="old('country_id', $item->country_id ?? null)"
        :options="\App\Models\Country::orderBy('internal_name')->get()"
        displayField="internal_name"
        placeholder="Select a country..."
        searchPlaceholder="Type to search countries..."
        :showId="true"
        entity="items"
    />
</x-form.field>

<x-form.field label="Legacy ID" name="backward_compatibility">
    <x-form.input 
        name="backward_compatibility" 
        :value="old('backward_compatibility', $item->backward_compatibility ?? '')" 
        placeholder="Optional legacy identifier" 
    />
</x-form.field>

</div>

<x-form.actions 
    :cancel-route="$item ? route('items.show', $item) : route('items.index')"
    entity="items"
/>

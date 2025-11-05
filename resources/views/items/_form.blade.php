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

<x-form.field label="Parent Item" name="parent_id">
    @if(isset($parent) && $parent)
        <div class="mb-2 p-3 bg-blue-50 border border-blue-200 rounded-md">
            <p class="text-sm text-blue-800">
                <strong>Parent:</strong> {{ $parent->internal_name }}
                <span class="text-xs text-blue-600">(pre-filled)</span>
            </p>
        </div>
        <input type="hidden" name="parent_id" value="{{ $parent->id }}" />
    @else
        <x-form.entity-select 
            name="parent_id" 
            :value="old('parent_id', $item->parent_id ?? null)"
            :options="\App\Models\Item::when(isset($item), fn($q) => $q->where('id', '!=', $item->id))
                ->orderBy('internal_name')->get()"
            displayField="internal_name"
            placeholder="No parent (top-level item)"
            searchPlaceholder="Type to search..."
            entity="items"
        />
        <x-slot name="help">
            Optional: Select a parent item to create a hierarchical relationship
        </x-slot>
    @endif
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

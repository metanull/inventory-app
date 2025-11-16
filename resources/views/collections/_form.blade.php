@csrf

<div class="p-6 space-y-6">
    <x-form.field label="Internal Name" name="internal_name" variant="gray" required>
    <x-form.input 
        name="internal_name" 
        :value="old('internal_name', $collection->internal_name ?? '')" 
        required 
    />
</x-form.field>

<x-form.field label="Type" name="type" required>
    <x-form.entity-select 
        name="type" 
        :value="old('type', $collection->type ?? '')"
        :options="collect([
            (object)['id' => 'collection', 'name' => 'Collection'],
            (object)['id' => 'exhibition', 'name' => 'Exhibition'],
            (object)['id' => 'gallery', 'name' => 'Gallery'],
            (object)['id' => 'theme', 'name' => 'Theme'],
            (object)['id' => 'exhibition trail', 'name' => 'Exhibition Trail'],
            (object)['id' => 'itinerary', 'name' => 'Itinerary'],
            (object)['id' => 'location', 'name' => 'Location']
        ])"
        displayField="name"
        valueField="id"
        placeholder="Select type..."
        searchPlaceholder="Type to search..."
        entity="collections"
        required
    />
</x-form.field>

<x-form.field label="Language" name="language_id" variant="gray" required>
    <x-form.entity-select 
        name="language_id" 
        :value="old('language_id', $collection->language_id ?? null)"
        :options="\App\Models\Language::orderBy('internal_name')->get()"
        displayField="internal_name"
        placeholder="Select a language..."
        searchPlaceholder="Type to search languages..."
        :showId="true"
        entity="collections"
        required
    />
</x-form.field>

<x-form.field label="Context" name="context_id" required>
    <x-form.entity-select 
        name="context_id" 
        :value="old('context_id', $collection->context_id ?? null)"
        :options="\App\Models\Context::orderBy('internal_name')->get()"
        displayField="internal_name"
        placeholder="Select a context..."
        searchPlaceholder="Type to search contexts..."
        entity="collections"
        required
    />
</x-form.field>

<x-form.field label="Parent Collection" name="parent_id" variant="gray">
    <x-form.entity-select 
        name="parent_id" 
        :value="old('parent_id', $collection->parent_id ?? null)"
        :options="\App\Models\Collection::orderBy('internal_name')->get()"
        displayField="internal_name"
        placeholder="Select a parent collection (optional)..."
        searchPlaceholder="Type to search collections..."
        entity="collections"
    />
</x-form.field>

<x-form.field label="Legacy ID" name="backward_compatibility" variant="gray">
    <x-form.input 
        name="backward_compatibility" 
        :value="old('backward_compatibility', $collection->backward_compatibility ?? '')" 
        placeholder="Optional legacy identifier"
    />
</x-form.field>
</div>

<x-form.actions 
    entity="collections" 
    :cancel-route="$collection ? route('collections.show', $collection) : route('collections.index')"
/>

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
    <x-form.select 
        name="type" 
        :value="old('type', $collection->type ?? '')"
        placeholder="Select type..."
        required
    >
        <option value="collection" @selected(old('type', $collection->type ?? '') === 'collection')>Collection</option>
        <option value="exhibition" @selected(old('type', $collection->type ?? '') === 'exhibition')>Exhibition</option>
        <option value="gallery" @selected(old('type', $collection->type ?? '') === 'gallery')>Gallery</option>
    </x-form.select>
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

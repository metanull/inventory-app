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
    <x-form.select 
        name="language_id" 
        :value="old('language_id', $collection->language_id ?? '')"
        placeholder="Select language..."
        required
    >
        @foreach($languages ?? [] as $language)
            <option value="{{ $language->id }}" @selected(old('language_id', $collection->language_id ?? '') == $language->id)>
                {{ $language->internal_name }} ({{ $language->id }})
            </option>
        @endforeach
    </x-form.select>
</x-form.field>

<x-form.field label="Context" name="context_id" required>
    <x-form.select 
        name="context_id" 
        :value="old('context_id', $collection->context_id ?? '')"
        placeholder="Select context..."
        required
    >
        @foreach($contexts ?? [] as $context)
            <option value="{{ $context->id }}" @selected(old('context_id', $collection->context_id ?? '') == $context->id)>
                {{ $context->internal_name }}
            </option>
        @endforeach
    </x-form.select>
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

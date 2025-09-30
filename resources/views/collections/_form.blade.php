@csrf

<dl>
    <x-form.field name="internal_name" label="Internal Name" variant="gray" required 
                  :value="$collection->internal_name ?? ''" />

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

    <x-form.language-select name="language_id" label="Language" variant="gray" required
                            :value="$collection->language_id ?? ''" :languages="$languages ?? []"
                            placeholder="Select language..." />

    <x-form.context-select name="context_id" label="Context" required
                           :value="$collection->context_id ?? ''" :contexts="$contexts ?? []"
                           placeholder="Select context..." />

    <x-form.field name="backward_compatibility" label="Legacy ID" variant="gray"
                  :value="$collection->backward_compatibility ?? ''" 
                  placeholder="Optional legacy identifier" />
</dl>

<x-form.actions entity="collections" 
                :cancel-route="isset($collection) ? route('collections.show', $collection) : route('collections.index')" />

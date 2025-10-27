@csrf

<div class="p-6 space-y-6">
    <x-form.field label="Internal Name" name="internal_name" variant="gray" required>
        <x-form.input 
            name="internal_name" 
            :value="old('internal_name', $tag->internal_name ?? '')" 
            required 
        />
    </x-form.field>

    <x-form.field label="Description" name="description" variant="gray" required>
        <x-form.textarea 
            name="description" 
            :value="old('description', $tag->description ?? '')"
            rows="4"
            required 
        />
    </x-form.field>

    <x-form.field label="Legacy ID" name="backward_compatibility">
        <x-form.input 
            name="backward_compatibility" 
            :value="old('backward_compatibility', $tag->backward_compatibility ?? '')" 
            placeholder="Optional legacy identifier" 
        />
    </x-form.field>
</div>

<x-form.actions 
    entity="tag" 
    :cancel-route="$tag ? route('tags.show', $tag) : route('tags.index')"
/>

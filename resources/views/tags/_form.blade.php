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
        <textarea 
            name="description" 
            rows="4"
            required 
            class="block w-full px-3 py-2 rounded-md shadow-sm sm:text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
        >{{ old('description', $tag->description ?? '') }}</textarea>
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

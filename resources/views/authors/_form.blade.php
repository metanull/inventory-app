@csrf

<div class="p-6 space-y-6">
    <div class="space-y-6">
        <x-form.field label="Name" name="name" variant="gray" required>
            <x-form.input 
                name="name" 
                :value="old('name', $author->name ?? '')" 
                required 
                placeholder="Author's full name"
            />
        </x-form.field>

        <x-form.field label="Internal Name" name="internal_name" variant="gray">
            <x-form.input 
                name="internal_name" 
                :value="old('internal_name', $author->internal_name ?? '')" 
                placeholder="Optional internal reference name"
            />
        </x-form.field>

        <x-form.field label="Legacy ID" name="backward_compatibility" variant="gray">
            <x-form.input 
                name="backward_compatibility" 
                :value="old('backward_compatibility', $author->backward_compatibility ?? '')" 
                placeholder="Optional legacy identifier"
            />
        </x-form.field>
    </div>
</div>

<x-form.actions 
    entity="authors" 
    :cancel-route="$author ? route('authors.show', $author) : route('authors.index')"
/>

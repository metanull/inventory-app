@csrf

<div class="p-6 space-y-6">
    <x-form.field label="Internal Name" name="internal_name" variant="gray" required>
        <x-form.input 
            name="internal_name" 
            :value="old('internal_name', $glossary->internal_name ?? '')" 
            required 
        />
    </x-form.field>

    <x-form.field label="Legacy ID" name="backward_compatibility">
        <x-form.input 
            name="backward_compatibility" 
            :value="old('backward_compatibility', $glossary->backward_compatibility ?? '')" 
            placeholder="Optional legacy identifier"
        />
    </x-form.field>
</div>

<x-form.actions 
    entity="glossary" 
    :cancel-route="$glossary ? route('glossaries.show', $glossary) : route('glossaries.index')"
/>

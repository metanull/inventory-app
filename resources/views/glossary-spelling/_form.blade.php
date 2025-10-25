@csrf

<div class="p-6 space-y-6">
    <x-form.field label="Language" name="language_id" variant="gray" required>
        <x-form.entity-select 
            name="language_id" 
            :value="old('language_id', ($spelling ?? null)?->language_id ?? null)"
            :options="$languages"
            displayField="internal_name"
            placeholder="Select a language..."
            searchPlaceholder="Type to search languages..."
            required
            :showId="true"
        />
    </x-form.field>

    <x-form.field label="Spelling" name="spelling" variant="gray" required>
        <x-form.input 
            name="spelling" 
            :value="old('spelling', ($spelling ?? null)?->spelling ?? '')" 
            required 
            placeholder="Enter the spelling variant"
        />
    </x-form.field>
</div>

<x-form.actions 
    entity="spelling" 
    :cancel-route="isset($spelling) ? route('glossaries.spellings.show', [$glossary, $spelling]) : route('glossaries.spellings.index', $glossary)"
/>

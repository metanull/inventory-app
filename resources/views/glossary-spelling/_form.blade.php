@csrf

<div class="p-6 space-y-6">
    <x-form.field label="Language" name="language_id" variant="gray" required>
        <select name="language_id" class="block w-full px-3 py-2 rounded-md shadow-sm sm:text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
            <option value="">Select a language</option>
            @foreach($languages as $language)
                <option value="{{ $language->id }}" {{ old('language_id', ($spelling ?? null)?->language_id ?? '') == $language->id ? 'selected' : '' }}>
                    {{ $language->internal_name }}
                </option>
            @endforeach
        </select>
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

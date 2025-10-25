@csrf

<div class="p-6 space-y-6">
    <x-form.field label="Language" name="language_id" variant="gray" required>
        <x-form.entity-select 
            name="language_id" 
            :value="old('language_id', ($translation ?? null)?->language_id ?? null)"
            :options="$languages->map(function($lang) use ($usedLanguageIds) {
                $lang->disabled = in_array($lang->id, $usedLanguageIds ?? []);
                return $lang;
            })"
            displayField="internal_name"
            placeholder="Select a language..."
            searchPlaceholder="Type to search languages..."
            required
            :showId="true"
        />
        @if(isset($usedLanguageIds) && count($usedLanguageIds) > 0)
            <p class="mt-1 text-sm text-gray-500">Some languages may not be available as they are already used for this glossary.</p>
        @endif
    </x-form.field>

    <x-form.field label="Definition" name="definition" variant="gray" required>
        <textarea 
            name="definition" 
            rows="6"
            required 
            class="block w-full px-3 py-2 rounded-md shadow-sm sm:text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
        >{{ old('definition', ($translation ?? null)?->definition ?? '') }}</textarea>
    </x-form.field>
</div>

<x-form.actions 
    entity="translation" 
    :cancel-route="isset($translation) ? route('glossaries.translations.show', [$glossary, $translation]) : route('glossaries.translations.index', $glossary)"
/>

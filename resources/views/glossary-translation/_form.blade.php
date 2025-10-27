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
        <x-form.textarea 
            name="definition" 
            :value="old('definition', ($translation ?? null)?->definition ?? '')"
            rows="6"
            required
        />
    </x-form.field>
</div>

<x-form.actions 
    entity="translation" 
    :cancel-route="isset($translation) ? route('glossaries.translations.show', [$glossary, $translation]) : route('glossaries.translations.index', $glossary)"
/>

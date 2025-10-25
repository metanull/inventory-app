@csrf

<div class="p-6 space-y-6">
    <x-form.field label="Language" name="language_id" variant="gray" required>
        <select name="language_id" class="block w-full px-3 py-2 rounded-md shadow-sm sm:text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>
            <option value="">Select a language</option>
            @foreach($languages as $language)
                @php
                    $isUsed = in_array($language->id, $usedLanguageIds ?? []);
                    $isSelected = old('language_id', ($translation ?? null)?->language_id ?? '') == $language->id;
                @endphp
                <option value="{{ $language->id }}" 
                        {{ $isSelected ? 'selected' : '' }}
                        {{ $isUsed ? 'disabled' : '' }}>
                    {{ $language->internal_name }}{{ $isUsed ? ' (already used)' : '' }}
                </option>
            @endforeach
        </select>
        @if(isset($usedLanguageIds) && count($usedLanguageIds) > 0)
            <p class="mt-1 text-sm text-gray-500">Languages with "(already used)" cannot be selected as they already have a translation.</p>
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

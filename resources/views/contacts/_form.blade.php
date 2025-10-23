@csrf

<div class="p-6 space-y-6">
    <div class="border-b border-gray-200 pb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
        
        <div class="space-y-6">
            <x-form.field label="Internal Name" name="internal_name" variant="gray" required>
                <x-form.input 
                    name="internal_name" 
                    :value="old('internal_name', $contact->internal_name ?? '')" 
                    required 
                    placeholder="Internal reference name"
                />
            </x-form.field>

            <x-form.field label="Phone Number" name="phone_number" variant="gray">
                <x-form.input 
                    name="phone_number" 
                    type="tel"
                    :value="old('phone_number', $contact->phone_number ?? '')" 
                    placeholder="+1234567890"
                />
            </x-form.field>

            <x-form.field label="Fax Number" name="fax_number" variant="gray">
                <x-form.input 
                    name="fax_number" 
                    type="tel"
                    :value="old('fax_number', $contact->fax_number ?? '')" 
                    placeholder="+1234567890"
                />
            </x-form.field>

            <x-form.field label="Email" name="email" variant="gray">
                <x-form.input 
                    name="email" 
                    type="email"
                    :value="old('email', $contact->email ?? '')" 
                    placeholder="contact@example.com"
                />
            </x-form.field>
        </div>
    </div>

    <div>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Translations (Labels)</h3>
        <p class="text-sm text-gray-600 mb-4">Add translated labels for this contact in different languages.</p>
        
        <div id="translations-container" class="space-y-4">
            @if($contact && $contact->translations->count() > 0)
                @foreach($contact->translations as $index => $translation)
                    <div class="translation-row flex gap-4 items-start">
                        <div class="flex-1">
                            <select name="translations[{{ $index }}][language_id]" class="w-full rounded-md border-gray-300">
                                <option value="">Select language...</option>
                                @foreach($languages as $language)
                                    <option value="{{ $language->id }}" @selected($translation->language_id === $language->id)>
                                        {{ $language->internal_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1">
                            <input type="text" name="translations[{{ $index }}][label]" value="{{ $translation->label }}" 
                                   placeholder="Label" class="w-full rounded-md border-gray-300">
                        </div>
                        <button type="button" onclick="this.closest('.translation-row').remove()" 
                                class="px-3 py-2 bg-red-100 text-red-700 rounded-md hover:bg-red-200">Remove</button>
                    </div>
                @endforeach
            @else
                <div class="translation-row flex gap-4 items-start">
                    <div class="flex-1">
                        <select name="translations[0][language_id]" class="w-full rounded-md border-gray-300">
                            <option value="">Select language...</option>
                            @foreach($languages as $language)
                                <option value="{{ $language->id }}">{{ $language->internal_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1">
                        <input type="text" name="translations[0][label]" placeholder="Label" class="w-full rounded-md border-gray-300">
                    </div>
                    <button type="button" onclick="this.closest('.translation-row').remove()" 
                            class="px-3 py-2 bg-red-100 text-red-700 rounded-md hover:bg-red-200">Remove</button>
                </div>
            @endif
        </div>
        
        <button type="button" onclick="addTranslation()" class="mt-4 px-4 py-2 bg-indigo-100 text-indigo-700 rounded-md hover:bg-indigo-200">
            Add Translation
        </button>
    </div>
</div>

<x-form.actions 
    entity="contacts" 
    :cancel-route="$contact ? route('contacts.show', $contact) : route('contacts.index')"
/>

<script>
let translationIndex = {{ $contact && $contact->translations->count() > 0 ? $contact->translations->count() : 1 }};

function addTranslation() {
    const container = document.getElementById('translations-container');
    const newRow = document.createElement('div');
    newRow.className = 'translation-row flex gap-4 items-start';
    newRow.innerHTML = `
        <div class="flex-1">
            <select name="translations[${translationIndex}][language_id]" class="w-full rounded-md border-gray-300">
                <option value="">Select language...</option>
                @foreach($languages as $language)
                    <option value="{{ $language->id }}">{{ $language->internal_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1">
            <input type="text" name="translations[${translationIndex}][label]" placeholder="Label" class="w-full rounded-md border-gray-300">
        </div>
        <button type="button" onclick="this.closest('.translation-row').remove()" 
                class="px-3 py-2 bg-red-100 text-red-700 rounded-md hover:bg-red-200">Remove</button>
    `;
    container.appendChild(newRow);
    translationIndex++;
}
</script>

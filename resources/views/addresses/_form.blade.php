@csrf

<div class="p-6 space-y-6">
    <div class="border-b border-gray-200 pb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
        
        <div class="space-y-6">
            <x-form.field label="Internal Name" name="internal_name" variant="gray" required>
                <x-form.input 
                    name="internal_name" 
                    :value="old('internal_name', $address->internal_name ?? '')" 
                    required 
                    placeholder="Internal reference name"
                />
            </x-form.field>

            <x-form.field label="Country" name="country_id" variant="gray" required>
                <x-form.country-select 
                    name="country_id"
                    :selected="old('country_id', $address->country_id ?? '')"
                    required
                />
            </x-form.field>
        </div>
    </div>

    <div>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Translations</h3>
        <p class="text-sm text-gray-600 mb-4">Add address details in different languages.</p>
        
        <div id="translations-container" class="space-y-4">
            @if($address && $address->translations->count() > 0)
                @foreach($address->translations as $index => $translation)
                    <div class="translation-row p-4 border border-gray-200 rounded-lg space-y-4">
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Language</label>
                                <select name="translations[{{ $index }}][language_id]" class="w-full rounded-md border-gray-300">
                                    <option value="">Select language...</option>
                                    @foreach($languages as $language)
                                        <option value="{{ $language->id }}" @selected($translation->language_id === $language->id)>
                                            {{ $language->internal_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="button" onclick="this.closest('.translation-row').remove()" 
                                        class="px-3 py-2 bg-red-100 text-red-700 rounded-md hover:bg-red-200">Remove</button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <textarea name="translations[{{ $index }}][address]" rows="2" 
                                      placeholder="Street address, city, postal code..." 
                                      class="w-full rounded-md border-gray-300">{{ $translation->address }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                            <textarea name="translations[{{ $index }}][description]" rows="2" 
                                      placeholder="Additional notes..." 
                                      class="w-full rounded-md border-gray-300">{{ $translation->description }}</textarea>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="translation-row p-4 border border-gray-200 rounded-lg space-y-4">
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Language</label>
                            <select name="translations[0][language_id]" class="w-full rounded-md border-gray-300">
                                <option value="">Select language...</option>
                                @foreach($languages as $language)
                                    <option value="{{ $language->id }}">{{ $language->internal_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="button" onclick="this.closest('.translation-row').remove()" 
                                    class="px-3 py-2 bg-red-100 text-red-700 rounded-md hover:bg-red-200">Remove</button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <textarea name="translations[0][address]" rows="2" 
                                  placeholder="Street address, city, postal code..." 
                                  class="w-full rounded-md border-gray-300"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                        <textarea name="translations[0][description]" rows="2" 
                                  placeholder="Additional notes..." 
                                  class="w-full rounded-md border-gray-300"></textarea>
                    </div>
                </div>
            @endif
        </div>
        
        <button type="button" onclick="addTranslation()" class="mt-4 px-4 py-2 bg-indigo-100 text-indigo-700 rounded-md hover:bg-indigo-200">
            Add Translation
        </button>
    </div>
</div>

<x-form.actions 
    entity="addresses" 
    :cancel-route="$address ? route('addresses.show', $address) : route('addresses.index')"
/>

<script>
let translationIndex = {{ $address && $address->translations->count() > 0 ? $address->translations->count() : 1 }};

function addTranslation() {
    const container = document.getElementById('translations-container');
    const newRow = document.createElement('div');
    newRow.className = 'translation-row p-4 border border-gray-200 rounded-lg space-y-4';
    newRow.innerHTML = `
        <div class="flex gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Language</label>
                <select name="translations[${translationIndex}][language_id]" class="w-full rounded-md border-gray-300">
                    <option value="">Select language...</option>
                    @foreach($languages as $language)
                        <option value="{{ $language->id }}">{{ $language->internal_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="button" onclick="this.closest('.translation-row').remove()" 
                        class="px-3 py-2 bg-red-100 text-red-700 rounded-md hover:bg-red-200">Remove</button>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
            <textarea name="translations[${translationIndex}][address]" rows="2" 
                      placeholder="Street address, city, postal code..." 
                      class="w-full rounded-md border-gray-300"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
            <textarea name="translations[${translationIndex}][description]" rows="2" 
                      placeholder="Additional notes..." 
                      class="w-full rounded-md border-gray-300"></textarea>
        </div>
    `;
    container.appendChild(newRow);
    translationIndex++;
}
</script>

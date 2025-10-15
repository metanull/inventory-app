@csrf

<div class="p-6 space-y-6">
    {{-- Required Fields --}}
    <div class="border-b border-gray-200 pb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
        
        <div class="space-y-6">
            <x-form.field label="Item" name="item_id" variant="gray" required>
                <select name="item_id" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select an item...</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}" @selected(old('item_id', $itemTranslation->item_id ?? '') === $item->id)>
                            {{ $item->internal_name }}
                        </option>
                    @endforeach
                </select>
            </x-form.field>

            <x-form.field label="Language" name="language_id" variant="gray" required>
                <select name="language_id" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select a language...</option>
                    @foreach($languages as $language)
                        <option value="{{ $language->id }}" @selected(old('language_id', $itemTranslation->language_id ?? ($defaultContext ?? '')) === $language->id)>
                            {{ $language->internal_name }}
                        </option>
                    @endforeach
                </select>
            </x-form.field>

            <x-form.field label="Context" name="context_id" variant="gray" required>
                <select name="context_id" required class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select a context...</option>
                    @foreach($contexts as $context)
                        <option value="{{ $context->id }}" @selected(old('context_id', $itemTranslation->context_id ?? ($defaultContext->id ?? '')) === $context->id)>
                            {{ $context->internal_name }}
                            @if($context->is_default) (default) @endif
                        </option>
                    @endforeach
                </select>
            </x-form.field>

            <x-form.field label="Name" name="name" variant="gray" required>
                <x-form.input 
                    name="name" 
                    :value="old('name', $itemTranslation->name ?? '')" 
                    required 
                />
            </x-form.field>

            <x-form.field label="Alternate Name" name="alternate_name" variant="gray">
                <x-form.input 
                    name="alternate_name" 
                    :value="old('alternate_name', $itemTranslation->alternate_name ?? '')" 
                />
            </x-form.field>

            <x-form.field label="Description" name="description" variant="gray" required>
                <textarea 
                    name="description" 
                    rows="4" 
                    required
                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                >{{ old('description', $itemTranslation->description ?? '') }}</textarea>
            </x-form.field>
        </div>
    </div>

    {{-- Object Details --}}
    <div class="border-b border-gray-200 pb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Object Details</h3>
        
        <div class="space-y-6">
            <x-form.field label="Type" name="type" variant="gray">
                <x-form.input 
                    name="type" 
                    :value="old('type', $itemTranslation->type ?? '')" 
                    placeholder="e.g., painting, sculpture, monument"
                />
            </x-form.field>

            <x-form.field label="Holder" name="holder" variant="gray">
                <textarea 
                    name="holder" 
                    rows="2"
                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Current holder of the item"
                >{{ old('holder', $itemTranslation->holder ?? '') }}</textarea>
            </x-form.field>

            <x-form.field label="Owner" name="owner" variant="gray">
                <textarea 
                    name="owner" 
                    rows="2"
                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Current owner of the item"
                >{{ old('owner', $itemTranslation->owner ?? '') }}</textarea>
            </x-form.field>

            <x-form.field label="Initial Owner" name="initial_owner" variant="gray">
                <textarea 
                    name="initial_owner" 
                    rows="2"
                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Original owner of the item"
                >{{ old('initial_owner', $itemTranslation->initial_owner ?? '') }}</textarea>
            </x-form.field>

            <x-form.field label="Dates" name="dates" variant="gray">
                <x-form.input 
                    name="dates" 
                    :value="old('dates', $itemTranslation->dates ?? '')" 
                    placeholder="e.g., 1920-1930, 18th century"
                />
            </x-form.field>

            <x-form.field label="Location" name="location" variant="gray">
                <x-form.input 
                    name="location" 
                    :value="old('location', $itemTranslation->location ?? '')" 
                    placeholder="Current or original location"
                />
            </x-form.field>

            <x-form.field label="Dimensions" name="dimensions" variant="gray">
                <x-form.input 
                    name="dimensions" 
                    :value="old('dimensions', $itemTranslation->dimensions ?? '')" 
                    placeholder="e.g., 100x80cm, 2m height"
                />
            </x-form.field>

            <x-form.field label="Place of Production" name="place_of_production" variant="gray">
                <x-form.input 
                    name="place_of_production" 
                    :value="old('place_of_production', $itemTranslation->place_of_production ?? '')" 
                    placeholder="Where the item was created"
                />
            </x-form.field>
        </div>
    </div>

    {{-- Research Information --}}
    <div class="border-b border-gray-200 pb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Research Information</h3>
        
        <div class="space-y-6">
            <x-form.field label="Method for Datation" name="method_for_datation" variant="gray">
                <textarea 
                    name="method_for_datation" 
                    rows="3"
                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Method used to date the item"
                >{{ old('method_for_datation', $itemTranslation->method_for_datation ?? '') }}</textarea>
            </x-form.field>

            <x-form.field label="Method for Provenance" name="method_for_provenance" variant="gray">
                <textarea 
                    name="method_for_provenance" 
                    rows="3"
                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Method used to determine provenance"
                >{{ old('method_for_provenance', $itemTranslation->method_for_provenance ?? '') }}</textarea>
            </x-form.field>

            <x-form.field label="Obtention" name="obtention" variant="gray">
                <textarea 
                    name="obtention" 
                    rows="3"
                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="How the item was obtained"
                >{{ old('obtention', $itemTranslation->obtention ?? '') }}</textarea>
            </x-form.field>

            <x-form.field label="Bibliography" name="bibliography" variant="gray">
                <textarea 
                    name="bibliography" 
                    rows="4"
                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Bibliographic references"
                >{{ old('bibliography', $itemTranslation->bibliography ?? '') }}</textarea>
            </x-form.field>
        </div>
    </div>

    {{-- Contributors --}}
    <div class="border-b border-gray-200 pb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Contributors</h3>
        <p class="text-sm text-gray-600 mb-4">Note: Author management will be implemented in a future update. These fields are currently unavailable.</p>
        
        <div class="space-y-6 opacity-50">
            <x-form.field label="Author" name="author_id" variant="gray">
                <select name="author_id" disabled class="w-full rounded-md border-gray-300 bg-gray-100">
                    <option value="">Select an author...</option>
                </select>
            </x-form.field>

            <x-form.field label="Text Copy Editor" name="text_copy_editor_id" variant="gray">
                <select name="text_copy_editor_id" disabled class="w-full rounded-md border-gray-300 bg-gray-100">
                    <option value="">Select a copy editor...</option>
                </select>
            </x-form.field>

            <x-form.field label="Translator" name="translator_id" variant="gray">
                <select name="translator_id" disabled class="w-full rounded-md border-gray-300 bg-gray-100">
                    <option value="">Select a translator...</option>
                </select>
            </x-form.field>

            <x-form.field label="Translation Copy Editor" name="translation_copy_editor_id" variant="gray">
                <select name="translation_copy_editor_id" disabled class="w-full rounded-md border-gray-300 bg-gray-100">
                    <option value="">Select a copy editor...</option>
                </select>
            </x-form.field>
        </div>
    </div>

    {{-- Additional Information --}}
    <div>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
        
        <div class="space-y-6">
            <x-form.field label="Extra Data (JSON)" name="extra" variant="gray">
                <textarea 
                    name="extra" 
                    rows="4"
                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm"
                    placeholder='{"custom": "data"}'
                >{{ is_array(old('extra')) ? json_encode(old('extra')) : old('extra', $itemTranslation->extra ?? '') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Must be valid JSON format</p>
            </x-form.field>

            <x-form.field label="Legacy ID" name="backward_compatibility" variant="gray">
                <x-form.input 
                    name="backward_compatibility" 
                    :value="old('backward_compatibility', $itemTranslation->backward_compatibility ?? '')" 
                    placeholder="Optional legacy identifier"
                />
            </x-form.field>
        </div>
    </div>
</div>

<x-form.actions 
    entity="item_translations" 
    :cancel-route="$itemTranslation ? route('item-translations.show', $itemTranslation) : route('item-translations.index')"
/>

@csrf

<div class="p-6 space-y-6">
    {{-- Required Fields --}}
    <div class="border-b border-gray-200 pb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
        
        <div class="space-y-6">
            <x-form.field label="Collection" name="collection_id" variant="gray" required>
                <x-form.entity-select 
                    name="collection_id" 
                    :value="old('collection_id', $collectionTranslation->collection_id ?? $selectedCollectionId ?? null)"
                    :options="$collections"
                    displayField="internal_name"
                    placeholder="Select a collection..."
                    searchPlaceholder="Type to search collections..."
                    required
                    entity="collections"
                />
            </x-form.field>

            <x-form.field label="Language" name="language_id" variant="gray" required>
                <x-form.entity-select 
                    name="language_id" 
                    :value="old('language_id', $collectionTranslation->language_id ?? null)"
                    :options="$languages"
                    displayField="internal_name"
                    placeholder="Select a language..."
                    searchPlaceholder="Type to search languages..."
                    required
                    :showId="true"
                />
            </x-form.field>

            <x-form.field label="Context" name="context_id" variant="gray" required>
                <x-form.entity-select 
                    name="context_id" 
                    :value="old('context_id', $collectionTranslation->context_id ?? ($defaultContext->id ?? null))"
                    :options="$contexts"
                    displayField="internal_name"
                    placeholder="Select a context..."
                    searchPlaceholder="Type to search contexts..."
                    required
                />
            </x-form.field>

            <x-form.field label="Title" name="title" variant="gray" required>
                <x-form.input 
                    name="title" 
                    :value="old('title', $collectionTranslation->title ?? '')" 
                    required 
                    placeholder="Translation title"
                />
            </x-form.field>

            <x-form.field label="Description" name="description" variant="gray">
                <textarea 
                    name="description" 
                    rows="4"
                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Description of the collection in this language"
                >{{ old('description', $collectionTranslation->description ?? '') }}</textarea>
            </x-form.field>

            <x-form.field label="URL" name="url" variant="gray">
                <x-form.input 
                    name="url" 
                    type="url"
                    :value="old('url', $collectionTranslation->url ?? '')" 
                    placeholder="https://example.com/collection"
                />
            </x-form.field>
        </div>
    </div>

    {{-- Additional Information --}}
    <div>
        <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
        
        <div class="space-y-6">
            <x-form.field label="Remarks" name="extra[remarks]" variant="gray">
                <textarea 
                    name="extra[remarks]" 
                    rows="4"
                    class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Additional notes or comments..."
                >{{ old('extra.remarks', $collectionTranslation->extra->remarks ?? '') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Additional notes or comments for this translation</p>
            </x-form.field>

            <x-form.field label="Legacy ID" name="backward_compatibility" variant="gray">
                <x-form.input 
                    name="backward_compatibility" 
                    :value="old('backward_compatibility', $collectionTranslation->backward_compatibility ?? '')" 
                    placeholder="Optional legacy identifier"
                />
            </x-form.field>
        </div>
    </div>
</div>

<x-form.actions 
    entity="collection_translations" 
    :cancel-route="$collectionTranslation ? route('collection-translations.show', $collectionTranslation) : route('collection-translations.index')"
/>

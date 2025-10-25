@csrf

<div class="p-6 space-y-6">
    {{-- Required Fields --}}
    <x-form.section heading="Basic Information">
        <x-form.field label="Item" name="item_id" variant="gray" required>
            <x-form.entity-select 
                name="item_id" 
                :value="old('item_id', $itemTranslation->item_id ?? $selectedItemId ?? null)"
                :options="$items"
                displayField="internal_name"
                placeholder="Select an item..."
                searchPlaceholder="Type to search items..."
                required
                entity="items"
            />
        </x-form.field>

        <x-form.field label="Language" name="language_id" variant="gray" required>
            <x-form.entity-select 
                name="language_id" 
                :value="old('language_id', $itemTranslation->language_id ?? ($defaultContext ?? null))"
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
                :value="old('context_id', $itemTranslation->context_id ?? ($defaultContext->id ?? null))"
                :options="$contexts"
                displayField="internal_name"
                placeholder="Select a context..."
                searchPlaceholder="Type to search contexts..."
                required
            />
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

        <x-form.field label="Description" name="description" variant="gray">
            <textarea 
                name="description" 
                rows="4"
                class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
            >{{ old('description', $itemTranslation->description ?? '') }}</textarea>
        </x-form.field>
    </x-form.section>

    {{-- Object Details --}}
    <x-form.section heading="Object Details">
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
        </x-form.section>

    {{-- Research Information --}}
    <x-form.section heading="Research Information">
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
        </x-form.section>

    {{-- Contributors --}}
    <x-form.section heading="Contributors">
        <x-form.field label="Author" name="author_id" variant="gray">
            <x-form.entity-select 
                name="author_id"
                :value="old('author_id', $itemTranslation->author_id ?? null)"
                :options="\App\Models\Author::orderBy('name')->get()"
                displayField="name"
                placeholder="Select an author..."
                searchPlaceholder="Type to search authors..."
            />
        </x-form.field>

        <x-form.field label="Text Copy Editor" name="text_copy_editor_id" variant="gray">
            <x-form.entity-select 
                name="text_copy_editor_id"
                :value="old('text_copy_editor_id', $itemTranslation->text_copy_editor_id ?? null)"
                :options="\App\Models\Author::orderBy('name')->get()"
                displayField="name"
                placeholder="Select a copy editor..."
                searchPlaceholder="Type to search authors..."
            />
            </x-form.field>

            <x-form.field label="Translator" name="translator_id" variant="gray">
                <x-form.entity-select 
                    name="translator_id"
                    :value="old('translator_id', $itemTranslation->translator_id ?? null)"
                    :options="\App\Models\Author::orderBy('name')->get()"
                    displayField="name"
                    placeholder="Select a translator..."
                    searchPlaceholder="Type to search authors..."
                />
            </x-form.field>

            <x-form.field label="Translation Copy Editor" name="translation_copy_editor_id" variant="gray">
                <x-form.entity-select 
                    name="translation_copy_editor_id"
                    :value="old('translation_copy_editor_id', $itemTranslation->translation_copy_editor_id ?? null)"
                    :options="\App\Models\Author::orderBy('name')->get()"
                    displayField="name"
                    placeholder="Select a translation copy editor..."
                    searchPlaceholder="Type to search authors..."
                />
            </x-form.field>
        </x-form.section>

    {{-- Additional Information --}}
    <x-form.section heading="Additional Information" :border="false">
        <x-form.field label="Remarks" name="extra[remarks]" variant="gray">
            <textarea 
                name="extra[remarks]" 
                rows="4"
                class="w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="Additional notes or comments..."
            >{{ old('extra.remarks', $itemTranslation->extra->remarks ?? '') }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Additional notes or comments for this translation</p>
        </x-form.field>

        <x-form.field label="Legacy ID" name="backward_compatibility" variant="gray">
            <x-form.input 
                name="backward_compatibility" 
                :value="old('backward_compatibility', $itemTranslation->backward_compatibility ?? '')" 
                placeholder="Optional legacy identifier"
            />
        </x-form.field>
    </x-form.section>
</div>

<x-form.actions 
    entity="item_translations" 
    :cancel-route="$itemTranslation ? route('item-translations.show', $itemTranslation) : route('item-translations.index')"
/>

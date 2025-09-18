@csrf

<dl>
    <x-form.field name="internal_name" label="Internal Name" variant="gray" required 
                  :value="$collection->internal_name ?? ''" />

    <x-form.language-select name="language_id" label="Language" required
                            :value="$collection->language_id ?? ''" :languages="$languages ?? []"
                            placeholder="Select language..." />

    <x-form.context-select name="context_id" label="Context" variant="gray" required
                           :value="$collection->context_id ?? ''" :contexts="$contexts ?? []"
                           placeholder="Select context..." />

    <x-form.field name="backward_compatibility" label="Legacy ID" 
                  :value="$collection->backward_compatibility ?? ''" 
                  placeholder="Optional legacy identifier" />
</dl>

<x-form.actions entity="collections" 
                :cancel-route="isset($collection) ? route('collections.show', $collection) : route('collections.index')" />

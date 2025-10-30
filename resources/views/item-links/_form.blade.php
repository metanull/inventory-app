@csrf

<div class="p-6 space-y-6">
    <x-form.section heading="Link Information">
        <x-form.field label="Target Item" name="target_id" variant="gray" required>
            <x-form.entity-select 
                name="target_id" 
                :value="old('target_id', $itemItemLink->target_id ?? null)"
                :options="$items"
                displayField="internal_name"
                placeholder="Select a target item..."
                searchPlaceholder="Type to search items..."
                required
                entity="items"
            />
        </x-form.field>

        <x-form.field label="Context" name="context_id" variant="gray" required>
            <x-form.entity-select 
                name="context_id" 
                :value="old('context_id', $itemItemLink->context_id ?? ($defaultContext->id ?? null))"
                :options="$contexts"
                displayField="internal_name"
                placeholder="Select a context..."
                searchPlaceholder="Type to search contexts..."
                required
            />
        </x-form.field>
    </x-form.section>
</div>

<x-form.actions 
    entity="item_item_links" 
    :cancel-route="$itemItemLink ? route('item-links.show', [$item, $itemItemLink]) : route('item-links.index', $item)"
/>

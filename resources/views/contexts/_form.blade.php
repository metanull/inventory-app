@csrf

<dl>
    <x-form.field name="internal_name" label="Internal Name" variant="gray" required 
                  :value="$context->internal_name ?? ''" />

    <x-form.checkbox name="is_default" label="Default" 
                     :checked="($context->is_default ?? false)">
        Mark as default
    </x-form.checkbox>

    <x-form.field name="backward_compatibility" label="Legacy ID" variant="gray"
                  :value="$context->backward_compatibility ?? ''" 
                  placeholder="Optional legacy identifier" />
</dl>

<x-form.actions entity="contexts" 
                :cancel-route="isset($context) ? route('contexts.show', $context) : route('contexts.index')" />

@csrf

<div class="p-6 space-y-6">
    <x-form.field label="Internal Name" name="internal_name" variant="gray" required>
        <x-form.input 
            name="internal_name" 
            :value="old('internal_name', $context->internal_name ?? '')" 
            required 
        />
    </x-form.field>

    <x-form.field label="Default" name="is_default">
        <x-form.checkbox 
            name="is_default" 
            :checked="old('is_default', $context->is_default ?? false)"
            label="Mark as default context"
        />
    </x-form.field>

    <x-form.field label="Legacy ID" name="backward_compatibility" variant="gray">
        <x-form.input 
            name="backward_compatibility" 
            :value="old('backward_compatibility', $context->backward_compatibility ?? '')" 
            placeholder="Optional legacy identifier"
        />
    </x-form.field>
</div>

<x-form.actions 
    entity="contexts" 
    :cancel-route="$context ? route('contexts.show', $context) : route('contexts.index')"
/>

@csrf

<x-form.field label="Code (3 letters)" name="id" variant="gray" required>
    <x-form.input 
        name="id" 
        :value="old('id', $language->id ?? '')" 
        :readonly="isset($language)"
        class="uppercase tracking-wide"
        maxlength="3"
        required 
    />
</x-form.field>

<x-form.field label="Internal Name" name="internal_name" required>
    <x-form.input 
        name="internal_name" 
        :value="old('internal_name', $language->internal_name ?? '')" 
        required 
    />
</x-form.field>

<x-form.field label="Legacy ID" name="backward_compatibility" variant="gray">
    <x-form.input 
        name="backward_compatibility" 
        :value="old('backward_compatibility', $language->backward_compatibility ?? '')" 
        maxlength="2"
    />
</x-form.field>

<x-form.field label="Default" name="is_default">
    <label class="inline-flex items-center space-x-2">
        <input 
            type="checkbox" 
            name="is_default" 
            value="1" 
            @checked(old('is_default', $language->is_default ?? false)) 
            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" 
        />
        <span class="text-sm text-gray-700">Mark as default language</span>
    </label>
</x-form.field>

<x-form.actions 
    :cancel-route="isset($language) ? route('languages.show', $language) : route('languages.index')"
    entity="languages"
/>
 

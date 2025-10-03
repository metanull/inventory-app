@csrf

<div class="p-6 space-y-6">
    <x-form.field label="Code (3 letters)" name="id" variant="gray" required>
    <x-form.input 
        name="id" 
        :value="old('id', $country->id ?? '')" 
        :readonly="isset($country)"
        class="uppercase tracking-wide"
        maxlength="3"
        required 
    />
</x-form.field>

<x-form.field label="Internal Name" name="internal_name" required>
    <x-form.input 
        name="internal_name" 
        :value="old('internal_name', $country->internal_name ?? '')" 
        required 
    />
</x-form.field>

<x-form.field label="Legacy ID" name="backward_compatibility" variant="gray">
    <x-form.input 
        name="backward_compatibility" 
        :value="old('backward_compatibility', $country->backward_compatibility ?? '')" 
        maxlength="2"
    />
</x-form.field>

</div>

<x-form.actions 
    :cancel-route="isset($country) ? route('countries.show', $country) : route('countries.index')"
    entity="countries"
/>
 

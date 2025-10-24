@csrf

<div class="p-6 space-y-6">
    <x-form.field label="Internal Name" name="internal_name" variant="gray" required>
    <x-form.input 
        name="internal_name" 
        :value="old('internal_name', $partner->internal_name ?? '')" 
        required 
    />
</x-form.field>

<x-form.field label="Type" name="type" required>
    <x-form.select 
        name="type" 
        :value="old('type', $partner->type ?? '')"
        placeholder="Select type..."
        required
    >
        <option value="museum" @selected(old('type', $partner->type ?? '') === 'museum')>Museum</option>
        <option value="institution" @selected(old('type', $partner->type ?? '') === 'institution')>Institution</option>
        <option value="individual" @selected(old('type', $partner->type ?? '') === 'individual')>Individual</option>
    </x-form.select>
</x-form.field>

<x-form.field label="Country" name="country_id" variant="gray">
    <livewire:country-select :value="old('country_id', $partner->country_id ?? null)" name="country_id" label="" />
</x-form.field>

<x-form.field label="Visible" name="visible">
    <x-form.checkbox 
        name="visible" 
        :checked="old('visible', $partner->visible ?? false)"
        label="Partner is visible to public"
    />
</x-form.field>

<div class="border-t pt-6">
    <h3 class="text-lg font-medium mb-4">GPS Location</h3>
    
    <x-form.field label="Latitude" name="latitude">
        <x-form.input 
            type="number"
            step="0.000001"
            name="latitude" 
            :value="old('latitude', $partner->latitude ?? '')" 
            placeholder="-90 to 90"
        />
    </x-form.field>

    <x-form.field label="Longitude" name="longitude">
        <x-form.input 
            type="number"
            step="0.000001"
            name="longitude" 
            :value="old('longitude', $partner->longitude ?? '')" 
            placeholder="-180 to 180"
        />
    </x-form.field>

    <x-form.field label="Map Zoom Level" name="map_zoom">
        <x-form.input 
            type="number"
            name="map_zoom" 
            :value="old('map_zoom', $partner->map_zoom ?? 16)" 
            placeholder="1-20"
            min="1"
            max="20"
        />
    </x-form.field>
</div>

<x-form.field label="Legacy ID" name="backward_compatibility">
    <x-form.input 
        name="backward_compatibility" 
        :value="old('backward_compatibility', $partner->backward_compatibility ?? '')" 
        placeholder="Optional legacy identifier" 
    />
</x-form.field>
</div>

<x-form.actions 
    :cancel-route="$partner ? route('partners.show', $partner) : route('partners.index')"
    entity="partners"
/>

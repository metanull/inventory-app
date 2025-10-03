@csrf

<div class="p-6 space-y-6">
    <x-form.field label="Internal Name" name="internal_name" variant="gray" required>
    <x-form.input 
        name="internal_name" 
        :value="old('internal_name', $project->internal_name ?? '')" 
        required 
    />
</x-form.field>

<x-form.date name="launch_date" label="Launch Date" 
             :value="old('launch_date', $project->launch_date ?? null)" />

<x-form.checkbox-group label="Flags" variant="gray">
    <x-form.checkbox-simple name="is_launched" label="Launched" 
                            :checked="old('is_launched', $project->is_launched ?? false)" />
    <x-form.checkbox-simple name="is_enabled" label="Enabled" 
                            :checked="old('is_enabled', $project->is_enabled ?? true)" class="ml-6" />
</x-form.checkbox-group>

<x-form.field label="Context" name="context_id">
    <x-form.select 
        name="context_id" 
        :value="old('context_id', $project->context_id ?? '')"
        placeholder="Select context..."
    >
        @foreach($contexts ?? [] as $context)
            <option value="{{ $context->id }}" @selected(old('context_id', $project->context_id ?? '') == $context->id)>
                {{ $context->internal_name }}
            </option>
        @endforeach
    </x-form.select>
</x-form.field>

<x-form.field label="Language" name="language_id" variant="gray">
    <x-form.select 
        name="language_id" 
        :value="old('language_id', $project->language_id ?? '')"
        placeholder="Select language..."
    >
        @foreach($languages ?? [] as $language)
            <option value="{{ $language->id }}" @selected(old('language_id', $project->language_id ?? '') == $language->id)>
                {{ $language->internal_name }} ({{ $language->id }})
            </option>
        @endforeach
    </x-form.select>
</x-form.field>

<x-form.field label="Legacy ID" name="backward_compatibility">
    <x-form.input 
        name="backward_compatibility" 
        :value="old('backward_compatibility', $project->backward_compatibility ?? '')" 
        placeholder="Optional legacy identifier"
    />
</x-form.field>
</div>

<x-form.actions 
    entity="projects" 
    :cancel-route="$project ? route('projects.show', $project) : route('projects.index')"
/>

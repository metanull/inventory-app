@csrf

<dl>
    <x-form.field name="internal_name" label="Internal Name" variant="gray" required 
                  :value="$project->internal_name ?? ''" />

    <x-form.date name="launch_date" label="Launch Date" 
                 :value="$project->launch_date ?? null" />

    <x-form.checkbox-group label="Flags" variant="gray">
        <x-form.checkbox-simple name="is_launched" label="Launched" 
                                :checked="($project->is_launched ?? false)" />
        <x-form.checkbox-simple name="is_enabled" label="Enabled" 
                                :checked="($project->is_enabled ?? true)" class="ml-6" />
    </x-form.checkbox-group>

    <x-form.context-select name="context_id" label="Context" 
                           :value="$project->context_id ?? ''" :contexts="$contexts ?? []" />

    <x-form.language-select name="language_id" label="Language" variant="gray"
                            :value="$project->language_id ?? ''" :languages="$languages ?? []" />

    <x-form.field name="backward_compatibility" label="Legacy ID" 
                  :value="$project->backward_compatibility ?? ''" 
                  placeholder="Optional legacy identifier" />
</dl>

<x-form.actions entity="projects" 
                :cancel-route="isset($project) ? route('projects.show', $project) : route('projects.index')" />

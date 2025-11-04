@props([
    'parentEntity',     // The parent model instance (e.g., $item, $partner)
    'parentType',       // Entity type name (e.g., 'item', 'partner')
    'showRoute',        // Route to parent show page
    'editRoute' => null, // Optional: route to parent edit page
])

@php
    $c = $entityColor(\Illuminate\Support\Str::plural($parentType));
    $entityLabel = ucfirst($parentType);
@endphp

<div class="mt-8">
    <x-layout.section :title="'Parent ' . $entityLabel" icon="link">
        <x-display.description-list>
            <x-display.field label="Internal Name">
                <a href="{{ $showRoute }}" class="{{ $c['accentLink'] }}">
                    {{ $parentEntity->internal_name }}
                </a>
            </x-display.field>
            
            <x-display.field label="ID">
                <x-format.uuid :uuid="$parentEntity->id" format="long" />
            </x-display.field>
            
            @if($parentEntity->backward_compatibility)
                <x-display.field label="Legacy ID" :value="$parentEntity->backward_compatibility" />
            @endif
        </x-display.description-list>
        
        <div class="mt-4 flex gap-2">
            <x-ui.button 
                :href="$showRoute" 
                variant="secondary" 
                :entity="\Illuminate\Support\Str::plural($parentType)"
                icon="eye">
                View {{ $entityLabel }}
            </x-ui.button>
            
            @if($editRoute)
                <x-ui.button 
                    :href="$editRoute" 
                    variant="warning" 
                    :entity="\Illuminate\Support\Str::plural($parentType)"
                    icon="pencil">
                    Edit {{ $entityLabel }}
                </x-ui.button>
            @endif
        </div>
    </x-layout.section>
</div>

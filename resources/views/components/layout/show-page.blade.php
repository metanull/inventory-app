@props([
    'entity' => '',
    'title' => '',
    'backRoute' => '',
    'editRoute' => '',
    'deleteRoute' => '',
    'deleteConfirm' => 'Are you sure you want to delete this record?',
    'backwardCompatibility' => null,
    'badges' => [],
])

@php($c = $entityColor($entity))

<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
    @if($backRoute)
        <div>
            <a href="{{ $backRoute }}" class="text-sm {{ $c['accentLink'] }}">&larr; Back to list</a>
        </div>
    @endif
    
    <x-entity.header :entity="$entity" :title="$title">
        @if($editRoute)
            <a href="{{ $editRoute }}" class="inline-flex items-center px-3 py-2 rounded-md {{ $c['button'] }} text-sm font-medium">Edit</a>
        @endif
        
        @if($deleteRoute)
            <form method="POST" action="{{ $deleteRoute }}" style="display: inline;" onsubmit="return confirm('{{ $deleteConfirm }}');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-3 py-2 rounded-md bg-red-600 hover:bg-red-700 text-white text-sm font-medium">Delete</button>
            </form>
        @endif
        
        @if($backwardCompatibility)
            <x-display.badge :entity="$entity">Legacy: {{ $backwardCompatibility }}</x-display.badge>
        @endif
        
        @foreach($badges as $badge)
            <x-display.badge :entity="$entity">{{ $badge }}</x-display.badge>
        @endforeach
    </x-entity.header>

    {{ $slot }}
</div>
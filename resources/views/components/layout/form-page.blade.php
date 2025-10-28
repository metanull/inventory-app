@props([
    'entity' => '',
    'title' => '',
    'backRoute' => '',
    'submitRoute' => '',
    'method' => 'POST',
])

@php($c = $entityColor($entity))

<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
    @if($backRoute)
        <div>
            <a href="{{ $backRoute }}" class="text-sm {{ $c['accentLink'] }}">&larr; {{ Str::contains($title, 'Edit') ? 'Back to detail' : 'Back to list' }}</a>
        </div>
    @endif
    
    <x-entity.header :entity="$entity" :title="$title" />

    <form method="POST" action="{{ $submitRoute }}" class="bg-white shadow sm:rounded-lg">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif
        
        {{ $slot }}
    </form>
</div>
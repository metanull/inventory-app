@props([
    'href' => null,
    'title' => '',
    'description' => '',
    'icon' => null,
    'iconColor' => 'indigo', // indigo, teal, red, blue, etc.
    'highlighted' => false, // For prominent cards
    'padding' => 'p-6', // Standardized padding
])

@php
    $baseClasses = 'group rounded-xl bg-white border transition flex flex-col ' . $padding;
    $borderClasses = $highlighted 
        ? "border-{$iconColor}-300 ring-1 ring-{$iconColor}-100 hover:ring-{$iconColor}-300"
        : 'border-gray-200 hover:border-' . $iconColor . '-300';
    $hoverClasses = 'hover:shadow';
    
    $iconBgClasses = $highlighted
        ? "bg-{$iconColor}-50 text-{$iconColor}-600 group-hover:bg-{$iconColor}-100"
        : "bg-{$iconColor}-50 text-{$iconColor}-600 group-hover:bg-{$iconColor}-100";
        
    $iconHoverClasses = "text-gray-400 group-hover:text-{$iconColor}-500";
    $linkClasses = "text-{$iconColor}-600 group-hover:text-{$iconColor}-700";
@endphp

@if($href)
    <a 
        href="{{ $href }}" 
        class="{{ $baseClasses }} {{ $borderClasses }} {{ $hoverClasses }}"
    >
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                @if($icon)
                    <span class="p-2 rounded-md {{ $iconBgClasses }}">
                        {!! $icon !!}
                    </span>
                @endif
                <h2 class="text-lg font-semibold text-gray-900">{{ $title }}</h2>
            </div>
            @if(isset($headerAction))
                {{ $headerAction }}
            @else
                <x-heroicon-o-eye class="w-5 h-5 {{ $iconHoverClasses }}" />
            @endif
        </div>
        <p class="text-sm text-gray-600 flex-1">{{ $description }}</p>
        @if(isset($footer))
            {{ $footer }}
        @else
            <span class="mt-4 inline-flex items-center text-sm font-medium {{ $linkClasses }}">
                {{ $slot->isEmpty() ? 'View' : $slot }} <span class="ml-1">&rarr;</span>
            </span>
        @endif
    </a>
@else
    <div class="{{ $baseClasses }} {{ $borderClasses }}">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                @if($icon)
                    <span class="p-2 rounded-md {{ $iconBgClasses }}">
                        {!! $icon !!}
                    </span>
                @endif
                <h2 class="text-lg font-semibold text-gray-900">{{ $title }}</h2>
            </div>
            @if(isset($headerAction))
                {{ $headerAction }}
            @endif
        </div>
        @if($description)
            <p class="text-sm text-gray-600 flex-1">{{ $description }}</p>
        @endif
        @if(!$slot->isEmpty())
            <div class="mt-4">
                {{ $slot }}
            </div>
        @endif
    </div>
@endif

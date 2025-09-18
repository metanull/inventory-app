@props([
    'placeholder' => 'Search...',
    'entity' => null,
    'mobileOptimized' => true,
])

@php
$c = $entity ? $entityColor($entity) : [];
$focusClasses = $c['focus'] ?? 'focus:ring-indigo-500 focus:border-indigo-500';

// Mobile optimization classes
$mobileClasses = $mobileOptimized ? 'touch:h-12 touch:text-base' : '';

$classes = implode(' ', [
    'w-full rounded-md border-gray-300 shadow-sm',
    $focusClasses,
    $mobileClasses,
    'sm:text-sm', // Keep normal size on larger screens
]);
@endphp

<div class="relative">
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <x-heroicon-o-magnifying-glass class="h-4 w-4 sm:h-5 sm:w-5 text-gray-400" />
    </div>
    <input 
        {{ $attributes->merge([
            'type' => 'text',
            'placeholder' => $placeholder,
            'class' => $classes
        ]) }}
        style="padding-left: 2.5rem;"
    />
    @if($attributes->get('wire:model.live.debounce.300ms'))
        <!-- Clear button for mobile -->
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
            <button 
                type="button" 
                class="text-gray-400 hover:text-gray-600 focus:outline-none touch:p-2"
                onclick="this.closest('.relative').querySelector('input').value = ''; this.closest('.relative').querySelector('input').dispatchEvent(new Event('input'));"
            >
                <x-heroicon-o-x-mark class="h-4 w-4" />
            </button>
        </div>
    @endif
</div>
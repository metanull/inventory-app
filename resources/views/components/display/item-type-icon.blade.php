{{--
    Item Type Icon Component
    Displays icon based on item type
    
    Usage:
    <x-display.item-type-icon :type="$item->type" class="w-4 h-4" />
--}}

@props([
    'type' => null,
    'class' => 'w-4 h-4',
])

@if($type === 'object')
    <x-heroicon-s-cube :class="$class" />
@elseif($type === 'monument')
    <x-heroicon-s-building-office-2 :class="$class" />
@elseif($type === 'detail')
    <x-heroicon-s-magnifying-glass-plus :class="$class" />
@elseif($type === 'picture')
    <x-heroicon-s-photo :class="$class" />
@else
    <x-heroicon-s-question-mark-circle :class="$class" />
@endif

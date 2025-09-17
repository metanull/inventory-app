@props([
  'type' => 'success', // success | warning | error | info
  'message' => null,
  'dismissible' => true,
])
@php
    $base = 'mb-4 p-3 rounded-md border text-sm flex items-start gap-2';
    $styles = [
        'success' => 'bg-green-50 border-green-200 text-green-700',
        'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
        'error'   => 'bg-red-50 border-red-200 text-red-700',
        'info'    => 'bg-blue-50 border-blue-200 text-blue-700',
    ];
    $iconMap = [
        'success' => 'check-circle',
        'warning' => 'exclamation-triangle',
        'error' => 'x-circle',
        'info' => 'information-circle',
    ];
    $icon = $iconMap[$type] ?? 'information-circle';
    $msg = $message ?? $slot ?? '';
@endphp
<div {{ $attributes->merge(['class' => $base.' '.($styles[$type] ?? $styles['info'])]) }} x-data="{ open: true }" x-show="open" x-transition>
    <div class="pt-0.5">
        @switch($icon)
            @case('check-circle') <x-heroicon-o-check-circle class="w-5 h-5" /> @break
            @case('exclamation-triangle') <x-heroicon-o-exclamation-triangle class="w-5 h-5" /> @break
            @case('x-circle') <x-heroicon-o-x-circle class="w-5 h-5" /> @break
            @default <x-heroicon-o-information-circle class="w-5 h-5" />
        @endswitch
    </div>
    <div class="flex-1">{!! e($msg) !!}</div>
    @if($dismissible)
        <button type="button" class="ml-2 text-current/60 hover:text-current" @click="open=false" aria-label="Dismiss">
            <x-heroicon-o-x-mark class="w-5 h-5" />
        </button>
    @endif
</div>

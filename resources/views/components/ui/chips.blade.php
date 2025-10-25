@props([
    'items' => [], // Array or Collection of selected items
    'displayField' => 'name',
    'wireRemove' => null, // Livewire method name to call for removal
    'color' => 'violet', // Badge color
])

@php
    $bgColor = "bg-{$color}-100";
    $textColor = "text-{$color}-800";
    $hoverColor = "hover:text-{$color}-900";
@endphp

@if(!empty($items) && count($items) > 0)
    <div class="flex flex-wrap gap-2">
        @foreach($items as $item)
            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm {{ $bgColor }} {{ $textColor }}">
                {{ is_object($item) ? $item->{$displayField} : $item[$displayField] }}
                @if($wireRemove)
                    <button 
                        wire:click="{{ $wireRemove }}('{{ is_object($item) ? $item->id : $item['id'] }}')" 
                        type="button" 
                        class="{{ $hoverColor }}"
                    >
                        <x-heroicon-s-x-mark class="w-4 h-4" />
                    </button>
                @endif
            </span>
        @endforeach
    </div>
@endif

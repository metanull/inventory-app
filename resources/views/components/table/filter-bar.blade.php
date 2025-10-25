@props([
    'wireModel' => 'q',
    'placeholder' => 'Search...',
    'clearable' => true,
])

<div class="flex flex-wrap items-center gap-3">
    <div class="relative">
        <input 
            wire:model.live.debounce.300ms="{{ $wireModel }}" 
            type="text" 
            placeholder="{{ $placeholder }}" 
            class="w-64 rounded-md border-gray-300 {{ $c['focus'] ?? 'focus:border-indigo-500 focus:ring-indigo-500' }}"
        />
    </div>
    
    @if($clearable)
        <button 
            wire:click="$set('{{ $wireModel }}','')" 
            type="button" 
            class="text-sm text-gray-600 hover:underline"
            x-show="$wire.{{ $wireModel }}"
            x-cloak
        >
            Clear
        </button>
    @endif

    {{ $slot }}
</div>

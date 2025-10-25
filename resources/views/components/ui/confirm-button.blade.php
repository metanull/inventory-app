@props([
    'action' => null,
    'method' => 'DELETE',
    'confirmMessage' => 'Are you sure?',
    'variant' => 'danger',
    'size' => 'sm',
    'icon' => 'trash',
    'entity' => null,
    'useLivewire' => false, // Set to true for Livewire-based confirmations
])

@if($useLivewire)
    {{-- Livewire event-based confirmation (for Livewire tables) --}}
    @php
        $sizes = [
            'xs' => 'px-2.5 py-1.5 text-xs',
            'sm' => 'px-3 py-2 text-sm',
        ];
        $palette = [
            'red' => 'border-red-200 text-red-600 hover:text-red-700 hover:bg-red-50',
            'indigo' => 'border-indigo-200 text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50',
            'gray' => 'border-gray-200 text-gray-600 hover:text-gray-800 hover:bg-gray-50',
        ];
        $color = $variant === 'danger' ? 'red' : ($variant === 'warning' ? 'indigo' : 'gray');
        $classes = ($sizes[$size] ?? $sizes['sm']).' inline-flex items-center rounded-md border bg-white font-medium transition '.$palette[$color];
    @endphp
    <button type="button" 
            x-data 
            @click="window.Livewire.dispatch('confirm-action', {
                title: @js($confirmMessage),
                message: 'This operation cannot be undone.',
                confirmLabel: 'Delete',
                cancelLabel: 'Cancel',
                action: @js($action),
                method: @js($method),
                color: @js($color)
            })" 
            class="{{ $classes }}">
        @if($icon === 'trash')<x-heroicon-o-trash class="w-4 h-4 mr-1" />@endif
        <span>{{ $slot }}</span>
    </button>
@else
    {{-- Alpine.js-based confirmation (for regular Blade forms) --}}
    <form method="POST" 
          action="{{ $action }}" 
          class="inline"
          x-data="{ 
              confirmed: false,
              submit() {
                  if (!this.confirmed) {
                      if (confirm('{{ addslashes($confirmMessage) }}')) {
                          this.confirmed = true;
                          this.$el.submit();
                      }
                  }
              }
          }"
          @submit.prevent="submit">
        @csrf
        @method($method)
        
        <x-ui.button 
            type="submit"
            :variant="$variant"
            :size="$size"
            :icon="$icon"
            :entity="$entity"
            {{ $attributes }}>
            {{ $slot }}
        </x-ui.button>
    </form>
@endif

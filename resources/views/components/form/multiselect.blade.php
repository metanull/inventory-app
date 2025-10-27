@props([
    'label' => '',
    'placeholder' => 'Type to search...',
    'options' => [], // Collection or array
    'displayField' => 'name',
    'descriptionField' => null, // Optional description field
    'wireModel' => '', // Livewire wire:model for selected IDs
    'entity' => null, // For entity color theming
    'chipColor' => 'violet', // Color for selected chips/badges
])

@php
    $colors = $entity ? $entityColor($entity) : null;
    $focusClasses = $colors ? "focus:border-{$colors['base']} focus:ring-{$colors['base']}" : 'focus:border-indigo-500 focus:ring-indigo-500';
    
    $chipBg = "bg-{$chipColor}-100";
    $chipText = "text-{$chipColor}-800";
    $chipHover = "hover:text-{$chipColor}-900";
    $chipHoverBg = "hover:bg-{$chipColor}-50";
@endphp

<div 
    x-data="{ 
        open: false, 
        search: '', 
        options: @js($options),
        selectedIds: @entangle($wireModel).live,
        get filteredOptions() {
            if (this.search === '') return this.options;
            const searchLower = this.search.toLowerCase();
            return this.options.filter(opt => {
                const displayValue = opt.{{ $displayField }}?.toLowerCase() || '';
                const isAlreadySelected = this.selectedIds.includes(opt.id);
                return displayValue.includes(searchLower) && !isAlreadySelected;
            });
        },
        addOption(option) {
            if (!this.selectedIds.includes(option.id)) {
                const newIds = [...this.selectedIds, option.id];
                this.selectedIds = newIds;
                $wire.set('{{ $wireModel }}', newIds);
            }
            this.search = '';
            this.open = false;
        },
        removeOption(optionId) {
            const newIds = this.selectedIds.filter(id => id !== optionId);
            this.selectedIds = newIds;
            $wire.set('{{ $wireModel }}', newIds);
        },
        clearAll() {
            this.selectedIds = [];
            $wire.set('{{ $wireModel }}', []);
        }
    }" 
    class="relative flex items-center gap-2"
>
    @if($label)
        <label class="text-sm font-medium text-gray-700">{{ $label }}</label>
    @endif
    
    <div class="relative w-64">
        <input 
            x-model="search"
            @focus="open = true"
            @click.away="open = false"
            @keydown.escape="open = false"
            type="text" 
            :placeholder="'{{ $placeholder }}'" 
            class="w-full rounded-md border-gray-300 text-sm {{ $focusClasses }}" 
        />
        
        <!-- Dropdown -->
        <div 
            x-show="open && filteredOptions.length > 0" 
            x-cloak
            class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
        >
            <template x-for="option in filteredOptions" :key="option.id">
                <div 
                    @click="addOption(option)" 
                    class="cursor-pointer select-none relative py-2 px-3 {{ $chipHoverBg }}"
                >
                    <span class="block truncate" x-text="option.{{ $displayField }}"></span>
                    @if($descriptionField)
                        <span class="block text-xs text-gray-500 truncate" x-text="option.{{ $descriptionField }}"></span>
                    @endif
                </div>
            </template>
        </div>
    </div>
    
    <button 
        type="button"
        x-show="selectedIds.length > 0" 
        @click="clearAll()" 
        class="text-sm text-gray-600 hover:underline"
        x-cloak
    >
        Clear all
    </button>
</div>

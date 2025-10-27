@props([
    'name' => '',
    'label' => '',
    'value' => null,
    'options' => [], // Collection or array of items with 'id' and display field
    'displayField' => 'name', // Field to display (e.g., 'internal_name', 'name')
    'placeholder' => 'Select an option...',
    'required' => false,
    'searchPlaceholder' => 'Type to search...',
    'showId' => false, // Show ID alongside name
    'entity' => null, // For entity color theming
    'class' => '',
    'wireModel' => null, // For Livewire integration
])

@php
    $inputId = $name . '-' . uniqid();
    $colors = $entity ? $entityColor($entity) : null;
    $focusClasses = $colors ? "focus:border-{$colors['base']} focus:ring-{$colors['base']}" : 'focus:border-indigo-500 focus:ring-indigo-500';
@endphp

<div 
    x-data="{
        open: false,
        search: '',
        selectedId: @if($wireModel) @entangle($wireModel).live @else '{{ old($name, $value) }}' @endif,
        options: {{ $options->toJson() }},
        get selectedOption() {
            return this.options.find(opt => opt.id == this.selectedId) || null;
        },
        get filteredOptions() {
            if (this.search === '') return this.options;
            const searchLower = this.search.toLowerCase();
            return this.options.filter(opt => {
                const displayValue = opt.{{ $displayField }}?.toLowerCase() || '';
                const idValue = opt.id?.toString().toLowerCase() || '';
                return displayValue.includes(searchLower) || idValue.includes(searchLower);
            });
        },
        selectOption(option) {
            this.selectedId = option.id;
            this.search = '';
            this.open = false;
            @if($wireModel)
            $wire.set('{{ $wireModel }}', option.id);
            @endif
        },
        clear() {
            this.selectedId = '';
            this.search = '';
            @if($wireModel)
            $wire.set('{{ $wireModel }}', '');
            @endif
        }
    }"
    class="relative {{ $class }}"
>
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}@if($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif
    
    <!-- Hidden input for form submission -->
    <input type="hidden" name="{{ $name }}" x-model="selectedId" @if($required) required @endif />
    
    <!-- Display/Search Input -->
    <div class="relative">
        <input 
            id="{{ $inputId }}"
            type="text"
            x-model="search"
            @focus="open = true"
            @click.away="open = false"
            @keydown.escape="open = false"
            :placeholder="selectedOption ? (selectedOption.{{ $displayField }} + (@json($showId) ? ' (' + selectedOption.id + ')' : '')) : '{{ $searchPlaceholder }}'"
            class="block w-full rounded-md border-gray-300 shadow-sm {{ $focusClasses }} sm:text-sm pr-10 {{ $class }}"
            autocomplete="off"
        />
        
        <!-- Clear/Dropdown Icon -->
        <div class="absolute inset-y-0 right-0 flex items-center pr-2 gap-1">
            <button 
                type="button" 
                x-show="selectedId" 
                @click.stop="clear()" 
                class="text-gray-400 hover:text-gray-600"
                x-cloak
            >
                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
            <svg class="h-5 w-5 text-gray-400 pointer-events-none" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </div>
    </div>
    
    <!-- Dropdown Options -->
    <div 
        x-show="open && filteredOptions.length > 0" 
        x-cloak
        class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
    >
        <template x-for="option in filteredOptions" :key="option.id">
            <div 
                @click="selectOption(option)" 
                class="cursor-pointer select-none relative py-2 px-3 hover:bg-gray-50"
                :class="{ 'bg-gray-100': selectedId == option.id }"
            >
                <span class="block truncate font-medium" x-text="option.{{ $displayField }}"></span>
                @if($showId)
                    <span class="block text-xs text-gray-500 truncate" x-text="'ID: ' + option.id"></span>
                @endif
            </div>
        </template>
    </div>
    
    <!-- No results message -->
    <div 
        x-show="open && search !== '' && filteredOptions.length === 0"
        x-cloak
        class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md py-3 px-3 text-sm text-gray-500 ring-1 ring-black ring-opacity-5"
    >
        No results found for "{{ '<span x-text="search"></span>' }}"
    </div>
    
    @if($name)
        @error($name)
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    @endif
</div>

<div class="relative">
    {{-- Hidden input for form submission --}}
    <input type="hidden" name="{{ $name }}" value="{{ $selectedId }}" @if($required) required @endif />
    
    {{-- Display/Search Input --}}
    <div class="relative">
        <input 
            type="text"
            wire:model.live.debounce.300ms="search"
            wire:focus="$set('open', true)"
            @click.away="$set('open', false)"
            @keydown.escape="$set('open', false)"
            placeholder="{{ $selectedOption ? $selectedOption->{$displayField} : $searchPlaceholder }}"
            class="block w-full rounded-md border-gray-300 shadow-sm {{ $focusClasses }} sm:text-sm pr-10"
            autocomplete="off"
        />
        
        {{-- Clear/Dropdown Icon --}}
        <div class="absolute inset-y-0 right-0 flex items-center pr-2 gap-1">
            @if($selectedId)
                <button 
                    type="button" 
                    wire:click="clear" 
                    class="text-gray-400 hover:text-gray-600"
                >
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            @endif
            <svg class="h-5 w-5 text-gray-400 pointer-events-none" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </div>
    </div>
    
    {{-- Dropdown Options --}}
    @if($open && $options->isNotEmpty())
        <div class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
            @foreach($options as $option)
                @php
                    $optionValue = is_object($option) ? ($option->{$valueField} ?? $option->id) : ($option[$valueField] ?? $option['id']);
                    $optionDisplay = is_object($option) ? ($option->{$displayField} ?? '') : ($option[$displayField] ?? '');
                @endphp
                <div 
                    wire:click="selectOption('{{ $optionValue }}')" 
                    class="cursor-pointer select-none relative py-2 px-3 hover:bg-gray-50 {{ $selectedId == $optionValue ? 'bg-gray-100' : '' }}"
                >
                    <span class="block truncate font-medium">{{ $optionDisplay }}</span>
                </div>
            @endforeach
        </div>
    @endif
    
    {{-- No results message --}}
    @if($open && $search !== '' && $options->isEmpty())
        <div class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md py-3 px-3 text-sm text-gray-500 ring-1 ring-black ring-opacity-5">
            No results found for "{{ $search }}"
        </div>
    @endif
    
    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

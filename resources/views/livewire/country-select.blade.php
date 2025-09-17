<div class="space-y-1">
    <label class="block text-sm font-medium text-gray-700">{{ $label }}</label>
    
    @if($allCountries->isEmpty())
        <div class="mb-2 p-3 rounded-md border border-amber-200 bg-amber-50 text-amber-800 text-sm flex items-start gap-2">
            <svg class="w-5 h-5 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            <div>No countries available. Please seed countries or contact an administrator.</div>
        </div>
    @endif

    <div class="relative">
        <input type="hidden" name="{{ $name }}" value="{{ $selected }}" />
        
        <button 
            type="button" 
            wire:click="toggleDropdown"
            @if($allCountries->isEmpty()) disabled @endif
            class="w-full px-3 py-2 rounded-md shadow-sm sm:text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-left bg-white flex justify-between items-center
                   @if($allCountries->isEmpty()) opacity-60 cursor-not-allowed @else hover:bg-gray-50 @endif
                   @if($open) ring-2 ring-offset-1 ring-indigo-500 @endif"
        >
            <span class="@if(!$selected) text-gray-400 @endif">
                @if($selectedCountry)
                    {{ $selectedCountry->internal_name }} ({{ $selectedCountry->id }})
                @elseif($allCountries->isEmpty())
                    No countries available
                @elseif($countries->isEmpty() && !empty($search))
                    No matches for "{{ $search }}"
                @else
                    {{ $placeholder }}
                @endif
            </span>
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
            </svg>
        </button>

        @if($open && !$allCountries->isEmpty())
            <div class="absolute z-20 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-hidden">
                <div class="p-2 border-b border-gray-100">
                    <input 
                        type="text" 
                        wire:model.live="search" 
                        placeholder="Search countries..." 
                        class="block w-full px-3 py-2 rounded-md shadow-sm sm:text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        wire:click.stop
                    />
                </div>
                
                <div class="py-1 text-sm max-h-48 overflow-y-auto">
                    @forelse($countries as $country)
                        <button 
                            type="button"
                            wire:click="selectCountry('{{ $country->id }}')"
                            class="w-full text-left px-3 py-2 hover:bg-gray-50 flex justify-between items-center
                                   @if($selected === $country->id) bg-indigo-50 @endif"
                        >
                            <span>
                                <span>{{ $country->internal_name }}</span>
                                <span class="text-xs text-gray-400 ml-1">({{ $country->id }})</span>
                            </span>
                            @if($selected === $country->id)
                                <svg class="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            @endif
                        </button>
                    @empty
                        <div class="px-3 py-2 text-sm text-gray-500 text-center">
                            @if(!empty($search))
                                No countries match "{{ $search }}"
                            @else
                                No countries found
                            @endif
                        </div>
                    @endforelse
                </div>
                
                <div class="p-2 border-t border-gray-100 flex justify-between">
                    <button type="button" wire:click="clear" class="text-xs text-gray-500 hover:text-gray-700">Clear</button>
                    <button type="button" wire:click="closeDropdown" class="text-xs text-indigo-600 hover:text-indigo-700">Close</button>
                </div>
            </div>
        @endif
    </div>
    
    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

<script>
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('[wire\\:id="{{ $this->getId() }}"]')) {
            @this.closeDropdown();
        }
    });
</script>
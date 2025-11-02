<div class="space-y-3">
    @foreach($pairs as $index => $pair)
    <div class="flex gap-2 items-end">
        <!-- Key Input -->
        <div class="flex-1">
            <label class="block text-xs font-medium text-gray-700">Key</label>
            <input 
                type="text"
                wire:model.live="pairs.{{ $index }}.key"
                placeholder="e.g., author, version"
                class="block w-full px-3 py-2 rounded-md shadow-sm text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
            />
        </div>
        
        <!-- Value Input -->
        <div class="flex-1">
            <label class="block text-xs font-medium text-gray-700">Value</label>
            <input 
                type="text"
                wire:model.live="pairs.{{ $index }}.value"
                placeholder="e.g., John Doe"
                class="block w-full px-3 py-2 rounded-md shadow-sm text-sm border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
            />
        </div>
        
        <!-- Delete Button -->
        <button 
            type="button"
            wire:click="removePair({{ $index }})"
            class="px-3 py-2 text-red-600 hover:text-red-900 text-sm font-medium"
            title="Delete this pair"
        >
            ✕
        </button>
    </div>
    @endforeach
    
    <!-- Add Row Button -->
    <button 
        type="button"
        wire:click="addPair"
        class="mt-2 inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
    >
        + Add Pair
    </button>
    
    <!-- Hidden inputs for form submission -->
    @foreach($pairs as $index => $pair)
        @if(!empty($pair['key']))
            <input type="hidden" name="{{ 'extra['.e($pair['key']).']' }}" value="{{ $pair['value'] }}" />
        @endif
    @endforeach
</div>

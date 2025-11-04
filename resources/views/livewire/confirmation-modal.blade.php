<div>
    @if($show)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data x-show="true">
            <!-- Modal backdrop -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                 wire:click="close"></div>
            
            <!-- Modal content -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $title }}</h3>
                    <p class="text-sm text-gray-500 mb-6">{{ $message }}</p>
                    
                    <div class="flex justify-end gap-3">
                        <button type="button" 
                                wire:click="close"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">
                            {{ $cancelLabel }}
                        </button>
                        
                        @if($action)
                            <form method="POST" action="{{ $action }}" class="inline">
                                @csrf
                                @method($method)
                                <button type="submit" 
                                        class="px-4 py-2 text-sm font-medium text-white rounded-md transition {{ $color === 'red' ? 'bg-red-600 hover:bg-red-700' : 'bg-indigo-600 hover:bg-indigo-700' }}">
                                    {{ $confirmLabel }}
                                </button>
                            </form>
                        @else
                            <button type="button" 
                                    wire:click="confirm"
                                    class="px-4 py-2 text-sm font-medium text-white rounded-md transition {{ $color === 'red' ? 'bg-red-600 hover:bg-red-700' : 'bg-indigo-600 hover:bg-indigo-700' }}">
                                {{ $confirmLabel }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

@props([
    'target' => null,
    'text' => 'Loading...'
])

<div {{ $target ? 'wire:loading.flex' : 'wire:loading' }}="{{ $target ? 'wire:target=' . $target : '' }}" 
     class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
     style="display: none;">
    <div class="bg-white rounded-lg p-6 shadow-xl max-w-sm mx-4">
        <div class="flex items-center space-x-3">
            <x-ui.loading size="md" color="blue" />
            <span class="text-gray-700 font-medium">{{ $text }}</span>
        </div>
    </div>
</div>
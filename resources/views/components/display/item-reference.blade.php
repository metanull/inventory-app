@props(['item'])

@if($item)
    <a 
        href="{{ route('items.show', $item) }}" 
        class="text-blue-600 hover:text-blue-800 underline"
    >
        {{ $item->internal_name }}
    </a>
@else
    <span class="text-gray-400">Not specified</span>
@endif

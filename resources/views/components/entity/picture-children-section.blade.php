@props(['items', 'collection' => null])

<div class="mt-8">
    <x-layout.section title="Pictures" icon="photo">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($items as $picture)
                @php($image = $picture->itemImages->first())
                <div class="bg-white rounded-lg overflow-hidden border border-gray-200 relative group">
                    <a
                        href="{{ $collection ? route('collections.items.show', [$collection, $picture]) : route('items.show', $picture) }}"
                        class="block"
                    >
                        <div class="aspect-square bg-gray-100 relative">
                            @if($image)
                                <img
                                    src="{{ route('items.item-images.view', [$picture, $image]) }}"
                                    alt="{{ $image->alt_text ?? $picture->internal_name }}"
                                    class="w-full h-full object-cover"
                                >
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <x-heroicon-o-photo class="w-10 h-10" />
                                </div>
                            @endif
                        </div>
                        <div class="p-3 space-y-1">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $picture->internal_name }}</p>
                            @if($picture->backward_compatibility)
                                <p class="text-xs text-gray-500">Legacy ID: {{ $picture->backward_compatibility }}</p>
                            @endif
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </x-layout.section>
</div>
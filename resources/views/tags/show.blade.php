@extends('layouts.app')

@section('content')
    <x-layout.show-page 
        entity="tags"
        :title="$tag->internal_name"
        :back-route="route('tags.index')"
        :edit-route="route('tags.edit', $tag)"
        :delete-route="route('tags.destroy', $tag)"
        delete-confirm="Are you sure you want to delete this tag?"
        :backward-compatibility="$tag->backward_compatibility"
    >
        @if(session('status'))
            <x-ui.alert :message="session('status')" type="success" entity="tags" />
        @endif

        <x-display.description-list>
            <x-display.field label="Internal Name" :value="$tag->internal_name" />
            <x-display.field label="Category" :value="$tag->category ? ucfirst($tag->category) : 'No category'" />
            <x-display.field label="Language" :value="$tag->language?->name ?? 'No language'" />
            <x-display.field label="Description" :value="$tag->description" />
        </x-display.description-list>

        <!-- Items with this Tag -->
        @php($itemCount = $tag->items()->count())
        <div class="mt-8">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Items with this Tag</h3>
                <span class="text-sm text-gray-500">{{ $itemCount }} {{ \Illuminate\Support\Str::plural('item', $itemCount) }}</span>
            </div>
            @if($itemCount > 0)
                @php($ic = $entityColor('items'))
                <a 
                    href="{{ route('items.index', ['selectedTags' => [$tag->id]]) }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white {{ $ic['button'] }}"
                >
                    <x-heroicon-o-funnel class="w-5 h-5 mr-2" />
                    View Items with this Tag
                </a>
            @else
                <p class="text-sm text-gray-500 italic">No items have this tag yet</p>
            @endif
        </div>

        <!-- System Properties -->
        <x-system-properties 
            :id="$tag->id"
            :backward-compatibility-id="$tag->backward_compatibility"
            :created-at="$tag->created_at"
            :updated-at="$tag->updated_at"
        />
    </x-layout.show-page>
@endsection

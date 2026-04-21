{{--
    Sidebar Card for Children Collections
    Shows children with counts and links
--}}

@props(['model', 'children'])

@php($tc = $entityColor('collections'))

<div class="bg-white rounded-lg border border-gray-200 shadow-sm p-4">
    <div class="flex items-center justify-between gap-2 mb-3">
        <h3 class="text-sm font-semibold text-gray-900">
            Children
            @if($children->isNotEmpty())
                <span class="text-xs font-normal text-gray-500">({{ $children->count() }})</span>
            @endif
        </h3>
        @can(\App\Enums\Permission::CREATE_DATA->value)
            <a href="{{ route('collections.create', ['parent_id' => $model->id]) }}"
               class="inline-flex items-center px-2 py-1 rounded text-xs {{ $tc['button'] }}"
               title="Create child collection">
                <x-heroicon-o-plus class="w-3 h-3" />
            </a>
        @endcan
    </div>

    @if($children->isEmpty())
        <p class="text-xs text-gray-500 italic">No child collections</p>
    @else
        <div class="space-y-2">
            @foreach($children as $child)
                <div class="flex items-center gap-2 p-2 rounded hover:bg-gray-50 transition-colors group">
                    <!-- Move Up/Down -->
                    @can(\App\Enums\Permission::UPDATE_DATA->value)
                        <div class="flex flex-col shrink-0">
                            <form method="POST" action="{{ route('collections.move-up', $child) }}">
                                @csrf
                                <button type="submit"
                                        class="transition-colors {{ $loop->first ? 'text-gray-200 cursor-not-allowed' : 'text-gray-400 hover:text-gray-600' }}"
                                        title="Move up" {{ $loop->first ? 'disabled' : '' }}>
                                    <x-heroicon-o-chevron-up class="w-3 h-3" />
                                </button>
                            </form>
                            <form method="POST" action="{{ route('collections.move-down', $child) }}">
                                @csrf
                                <button type="submit"
                                        class="transition-colors {{ $loop->last ? 'text-gray-200 cursor-not-allowed' : 'text-gray-400 hover:text-gray-600' }}"
                                        title="Move down" {{ $loop->last ? 'disabled' : '' }}>
                                    <x-heroicon-o-chevron-down class="w-3 h-3" />
                                </button>
                            </form>
                        </div>
                    @endcan

                    <!-- Content -->
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('collections.show', $child) }}"
                           class="text-xs font-medium {{ $tc['accentLink'] }} hover:underline truncate block">
                            {{ $child->internal_name }}
                        </a>
                        <p class="text-xs text-gray-500">{{ ucfirst($child->type) }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

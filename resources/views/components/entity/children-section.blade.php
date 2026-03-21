@props(['model'])

@php($tc = $entityColor('collections'))
@php($children = $model->children()->orderBy('display_order')->get())

<div class="mt-8">
    <x-layout.section title="Child Collections" icon="squares-2x2">
        @if($children->isEmpty())
            <p class="text-sm text-gray-500 italic">No child collections</p>
        @else
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    @foreach($children as $child)
                        <li class="px-6 py-4 hover:bg-gray-50 group">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3 min-w-0">
                                    <!-- Move Up -->
                                    @can(\App\Enums\Permission::UPDATE_DATA->value)
                                        <form method="POST" action="{{ route('collections.move-up', $child) }}" class="shrink-0">
                                            @csrf
                                            <button type="submit" class="text-gray-400 hover:text-gray-600 transition-colors" title="Move up">
                                                <x-heroicon-o-chevron-up class="w-4 h-4" />
                                            </button>
                                        </form>
                                    @endcan

                                    <!-- Move Down -->
                                    @can(\App\Enums\Permission::UPDATE_DATA->value)
                                        <form method="POST" action="{{ route('collections.move-down', $child) }}" class="shrink-0">
                                            @csrf
                                            <button type="submit" class="text-gray-400 hover:text-gray-600 transition-colors" title="Move down">
                                                <x-heroicon-o-chevron-down class="w-4 h-4" />
                                            </button>
                                        </form>
                                    @endcan

                                    <!-- Name + Type -->
                                    <div class="min-w-0">
                                        <a href="{{ route('collections.show', $child) }}" class="{{ $tc['accentLink'] }} font-medium">
                                            {{ $child->internal_name }}
                                        </a>
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ ucfirst($child->type) }}
                                        </span>
                                        @if($child->display_order !== null)
                                            <span class="ml-1 text-xs text-gray-400">#{{ $child->display_order }}</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- View link -->
                                <a href="{{ route('collections.show', $child) }}" class="text-gray-400 hover:text-gray-600 shrink-0" title="View">
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                </a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </x-layout.section>
</div>

@props(['model'])

@php($tc = $entityColor('item-item-links'))
<div class="mt-8">
    <!-- Outgoing Links Section -->
    <x-layout.section title="Links to Other Items" icon="arrow-right">
        @can(\App\Enums\Permission::CREATE_DATA->value)
            <x-slot name="action">
                <a href="{{ route('item-links.create', $model) }}" class="inline-flex items-center px-3 py-2 rounded-md {{ $tc['button'] }} text-sm font-medium">
                    <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                    Add Link
                </a>
            </x-slot>
        @endcan

        @if($model->outgoingLinks->isEmpty())
            <p class="text-sm text-gray-500 italic">No outgoing links</p>
        @else
            @php($groupedLinks = $model->outgoingLinks->groupBy('context_id'))
            @foreach($groupedLinks as $contextId => $contextLinks)
                @php($context = $contextLinks->first()->context)
                <div class="mb-6 last:mb-0">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                        <x-heroicon-o-folder class="w-4 h-4 mr-2" />
                        {{ $context->internal_name }}
                        <span class="ml-2 text-xs text-gray-500 font-normal">({{ $contextLinks->count() }} {{ Str::plural('link', $contextLinks->count()) }})</span>
                    </h4>
                    <div class="bg-white shadow overflow-hidden sm:rounded-md ml-6">
                        <ul class="divide-y divide-gray-200">
                            @foreach($contextLinks as $link)
                                <li class="px-6 py-4">
                                    <div class="flex items-start space-x-4">
                                        <!-- Thumbnail -->
                                        <div class="flex-shrink-0 w-12 h-12">
                                            @if($image = $link->target->itemImages->first())
                                                <img src="{{ Storage::url($image->image_path) }}" 
                                                     alt="{{ $link->target->internal_name }}"
                                                     class="w-12 h-12 rounded object-cover">
                                            @else
                                                <div class="w-12 h-12 rounded bg-gray-100 flex items-center justify-center">
                                                    <x-heroicon-o-photo class="w-6 h-6 text-gray-400" />
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Content -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('items.show', $link->target) }}" 
                                                   class="text-blue-600 hover:text-blue-900 font-medium">
                                                    {{ $link->target->internal_name }}
                                                </a>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                    @if($link->target->type === 'object')
                                                        <x-heroicon-s-cube class="w-3 h-3 mr-1" />
                                                    @elseif($link->target->type === 'monument')
                                                        <x-heroicon-s-building-office-2 class="w-3 h-3 mr-1" />
                                                    @elseif($link->target->type === 'detail')
                                                        <x-heroicon-s-magnifying-glass-plus class="w-3 h-3 mr-1" />
                                                    @else
                                                        <x-heroicon-s-photo class="w-3 h-3 mr-1" />
                                                    @endif
                                                    {{ ucfirst($link->target->type) }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Actions -->
                                        @can(\App\Enums\Permission::UPDATE_DATA->value)
                                            <div class="flex-shrink-0">
                                                <x-ui.confirm-button 
                                                    action="{{ route('item-links.destroy', [$model, $link]) }}"
                                                    method="DELETE"
                                                    variant="danger"
                                                    size="sm"
                                                    icon="x-mark"
                                                    :title="'Delete link'"
                                                    message="Are you sure you want to delete this link?">
                                                    <span class="sr-only">Delete</span>
                                                </x-ui.confirm-button>
                                            </div>
                                        @endcan
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        @endif
    </x-layout.section>

    <!-- Incoming Links Section -->
    <x-layout.section title="Links from Other Items" icon="arrow-left" class="mt-8">
        @if($model->incomingLinks->isEmpty())
            <p class="text-sm text-gray-500 italic">No incoming links</p>
        @else
            @php($groupedIncomingLinks = $model->incomingLinks->groupBy('context_id'))
            @foreach($groupedIncomingLinks as $contextId => $contextLinks)
                @php($context = $contextLinks->first()->context)
                <div class="mb-6 last:mb-0">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                        <x-heroicon-o-folder class="w-4 h-4 mr-2" />
                        {{ $context->internal_name }}
                        <span class="ml-2 text-xs text-gray-500 font-normal">({{ $contextLinks->count() }} {{ Str::plural('link', $contextLinks->count()) }})</span>
                    </h4>
                    <div class="bg-white shadow overflow-hidden sm:rounded-md ml-6">
                        <ul class="divide-y divide-gray-200">
                            @foreach($contextLinks as $link)
                                <li class="px-6 py-4">
                                    <div class="flex items-start space-x-4">
                                        <!-- Thumbnail -->
                                        <div class="flex-shrink-0 w-12 h-12">
                                            @if($image = $link->source->itemImages->first())
                                                <img src="{{ Storage::url($image->image_path) }}" 
                                                     alt="{{ $link->source->internal_name }}"
                                                     class="w-12 h-12 rounded object-cover">
                                            @else
                                                <div class="w-12 h-12 rounded bg-gray-100 flex items-center justify-center">
                                                    <x-heroicon-o-photo class="w-6 h-6 text-gray-400" />
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Content -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('items.show', $link->source) }}" 
                                                   class="text-blue-600 hover:text-blue-900 font-medium">
                                                    {{ $link->source->internal_name }}
                                                </a>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                    @if($link->source->type === 'object')
                                                        <x-heroicon-s-cube class="w-3 h-3 mr-1" />
                                                    @elseif($link->source->type === 'monument')
                                                        <x-heroicon-s-building-office-2 class="w-3 h-3 mr-1" />
                                                    @elseif($link->source->type === 'detail')
                                                        <x-heroicon-s-magnifying-glass-plus class="w-3 h-3 mr-1" />
                                                    @else
                                                        <x-heroicon-s-photo class="w-3 h-3 mr-1" />
                                                    @endif
                                                    {{ ucfirst($link->source->type) }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <!-- Actions -->
                                        @can(\App\Enums\Permission::UPDATE_DATA->value)
                                            <div class="flex-shrink-0">
                                                <x-ui.confirm-button 
                                                    action="{{ route('item-links.destroy', [$link->source, $link]) }}"
                                                    method="DELETE"
                                                    variant="danger"
                                                    size="sm"
                                                    icon="x-mark"
                                                    :title="'Delete link'"
                                                    message="Are you sure you want to delete this link?">
                                                    <span class="sr-only">Delete</span>
                                                </x-ui.confirm-button>
                                            </div>
                                        @endcan
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        @endif
    </x-layout.section>
</div>

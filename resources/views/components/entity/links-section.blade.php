@props(['model'])

@php($tc = $entityColor('item-item-links'))
<div class="mt-8">
    <x-layout.section title="Links" icon="link">
        @can(\App\Enums\Permission::CREATE_DATA->value)
            <x-slot name="action">
                <a href="{{ route('item-links.create', $model) }}" class="inline-flex items-center px-3 py-2 rounded-md {{ $tc['button'] }} text-sm font-medium">
                    <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                    Add Link
                </a>
            </x-slot>
        @endcan

        <!-- Links List -->
        @if($model->outgoingLinks->isEmpty())
            <p class="text-sm text-gray-500 italic">No links defined</p>
        @else
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    @foreach($model->outgoingLinks->take(5) as $link)
                        <li class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <a href="{{ route('item-links.show', [$model, $link]) }}" class="block hover:bg-gray-50 -mx-6 -my-4 px-6 py-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm text-gray-600">
                                                    <span class="font-medium">Links to</span>
                                                    <a href="{{ route('items.show', $link->target) }}" class="text-blue-600 hover:text-blue-900 font-medium">
                                                        {{ $link->target->internal_name }}
                                                    </a>
                                                </p>
                                                <p class="text-xs text-gray-500 mt-1">Context: {{ $link->context->internal_name }}</p>
                                            </div>
                                            <x-heroicon-o-arrow-right class="w-5 h-5 text-gray-400" />
                                        </div>
                                    </a>
                                </div>
                                @can(\App\Enums\Permission::UPDATE_DATA->value)
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
                                @endcan
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        @if($model->outgoingLinks->count() > 5)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <a href="{{ route('item-links.index', $model) }}" class="text-sm font-medium {{ $tc['accentLink'] }}">
                    View all {{ $model->outgoingLinks->count() }} links →
                </a>
            </div>
        @endif
    </x-layout.section>
</div>

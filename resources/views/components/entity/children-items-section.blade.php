@props(['model'])

@php($tc = $entityColor('items'))
<div class="mt-8">
    <x-layout.section title="Children Items" icon="arrow-down-circle">
        @can(\App\Enums\Permission::CREATE_DATA->value)
            <x-slot name="action">
                <a href="{{ route('items.create', ['parent_id' => $model->id]) }}" 
                   class="inline-flex items-center px-3 py-2 rounded-md {{ $tc['button'] }} text-sm font-medium">
                    <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                    Add Child
                </a>
            </x-slot>
        @endcan
        
        @if($model->children->isEmpty())
            <p class="text-sm text-gray-500 italic">No child items</p>
        @else
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    @foreach($model->children as $child)
                        <li class="px-6 py-4 hover:bg-gray-50">
                            <div class="flex items-start space-x-4">
                                <!-- Thumbnail -->
                                <div class="shrink-0 w-12 h-12">
                                    @if($image = $child->itemImages->first())
                                        <img src="{{ Storage::url($image->image_path) }}" 
                                             alt="{{ $child->internal_name }}"
                                             class="w-12 h-12 rounded object-cover">
                                    @else
                                        <div class="w-12 h-12 rounded bg-gray-100 flex items-center justify-center">
                                            <x-heroicon-o-photo class="w-6 h-6 text-gray-400" />
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <a href="{{ route('items.show', $child) }}" 
                                       class="text-blue-600 hover:text-blue-900 font-medium">
                                        {{ $child->internal_name }}
                                    </a>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            <x-display.item-type-icon :type="$child->type" class="w-3 h-3 mr-1" />
                                            {{ ucfirst($child->type) }}
                                        </span>
                                    </div>
                                    @if($child->backward_compatibility)
                                        <p class="text-xs text-gray-500 mt-1">Legacy ID: {{ $child->backward_compatibility }}</p>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </x-layout.section>
</div>

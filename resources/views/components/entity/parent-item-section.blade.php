@props(['model'])

@php($tc = $entityColor('items'))
<div class="mt-8">
    <x-layout.section title="Parent Item" icon="arrow-up-circle">
        @can(\App\Enums\Permission::UPDATE_DATA->value)
            <x-slot name="action">
                <form action="{{ route('items.removeParent', $model) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button 
                        type="submit" 
                        class="text-sm text-red-600 hover:text-red-900 font-medium"
                        onclick="return confirm('Remove parent relationship?')">
                        Remove Parent
                    </button>
                </form>
            </x-slot>
        @endcan
        
        <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-md">
            <!-- Thumbnail -->
            <div class="shrink-0 w-12 h-12">
                @if($image = $model->parent->itemImages->first())
                    <img src="{{ Storage::url($image->image_path) }}" 
                         alt="{{ $model->parent->internal_name }}"
                         class="w-12 h-12 rounded object-cover">
                @else
                    <div class="w-12 h-12 rounded bg-gray-200 flex items-center justify-center">
                        <x-heroicon-o-photo class="w-6 h-6 text-gray-400" />
                    </div>
                @endif
            </div>
            
            <!-- Content -->
            <div class="flex-1">
                <a href="{{ route('items.show', $model->parent) }}" 
                   class="text-blue-600 hover:text-blue-900 font-medium text-lg">
                    {{ $model->parent->internal_name }}
                </a>
                <div class="mt-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                        <x-display.item-type-icon :type="$model->parent->type" class="w-3 h-3 mr-1" />
                        {{ $model->parent->type->label() }}
                    </span>
                </div>
                @if($model->parent->backward_compatibility)
                    <p class="text-xs text-gray-500 mt-1">Legacy ID: {{ $model->parent->backward_compatibility }}</p>
                @endif
            </div>
        </div>
    </x-layout.section>
</div>

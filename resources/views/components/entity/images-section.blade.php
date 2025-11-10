@props([
    'entity',           // e.g., 'items', 'partners', 'collections'
    'model',            // The model instance ($item, $partner, $collection)
    'relationship' => null, // Optional override for relationship method name
])

@php
    $entitySingular = \Illuminate\Support\Str::singular($entity);
    $relationship = $relationship ?? $entitySingular . 'Images';
    $routePrefix = $entity . '.' . $entitySingular . '-images';
    $images = $model->{$relationship}()->orderBy('display_order')->get();
@endphp

<div class="mt-8">
    <x-layout.section title="Images" icon="photo">
        <x-slot name="action">
            <x-ui.button 
                href="{{ route($routePrefix . '.create', $model) }}" 
                variant="primary" 
                :entity="$entity"
                icon="plus">
                Attach Image
            </x-ui.button>
        </x-slot>

        @if($images->isEmpty())
            <x-ui.empty-state 
                icon="photo"
                title="No images"
                message="Get started by attaching an image to this {{ $entitySingular }}.">
                <x-ui.button 
                    href="{{ route($routePrefix . '.create', $model) }}" 
                    variant="primary" 
                    :entity="$entity">
                    Attach First Image
                </x-ui.button>
            </x-ui.empty-state>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($images as $image)
                    <div class="bg-white rounded-lg overflow-hidden border border-gray-200 relative group">
                        <!-- Image -->
                        <div class="aspect-square bg-gray-200 relative">
                            <img src="{{ route($routePrefix . '.view', [$model, $image]) }}" 
                                 alt="{{ $image->alt_text ?? ucfirst($entitySingular) . ' image' }}"
                                 class="w-full h-full object-cover">
                            
                            <!-- Detach Button (overlaid on image) -->
                            @can(\App\Enums\Permission::UPDATE_DATA->value)
                                <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <x-ui.confirm-button 
                                        :action="route($routePrefix . '.detach', [$model, $image])"
                                        confirmMessage="Detach this image?"
                                        variant="danger"
                                        icon="x-mark"
                                        entity="{{ $entity }}"
                                        class="bg-white/90! hover:bg-white! shadow-md">
                                    </x-ui.confirm-button>
                                </div>
                            @endcan
                        </div>

                        <!-- Alt Text with Controls -->
                        <div class="p-3 relative">
                            <div class="flex items-start gap-2">
                                <!-- Move Up/Down -->
                                @can(\App\Enums\Permission::UPDATE_DATA->value)
                                    <form method="POST" action="{{ route($routePrefix . '.move-up', [$model, $image]) }}" class="shrink-0">
                                        @csrf
                                        <button type="submit" class="text-gray-400 hover:text-gray-600 transition-colors" title="Move up">
                                            <x-heroicon-o-chevron-left class="w-4 h-4" />
                                        </button>
                                    </form>
                                @endcan

                                <!-- Alt Text -->
                                <div class="flex-1 min-w-0 relative">
                                    <p class="text-sm text-gray-900">
                                        {{ $image->alt_text ?: 'No alt text' }}
                                    </p>
                                    
                                    <!-- Edit Button (overlaid on text) -->
                                    @can(\App\Enums\Permission::UPDATE_DATA->value)
                                        <a href="{{ route($routePrefix . '.edit', [$model, $image]) }}" 
                                           class="absolute top-0 right-0 text-gray-400 hover:text-blue-600 transition-colors opacity-0 group-hover:opacity-100"
                                           title="Edit">
                                            <x-heroicon-o-pencil class="w-3 h-3" />
                                        </a>
                                    @endcan
                                </div>

                                <!-- Move Down -->
                                @can(\App\Enums\Permission::UPDATE_DATA->value)
                                    <form method="POST" action="{{ route($routePrefix . '.move-down', [$model, $image]) }}" class="shrink-0">
                                        @csrf
                                        <button type="submit" class="text-gray-400 hover:text-gray-600 transition-colors" title="Move down">
                                            <x-heroicon-o-chevron-right class="w-4 h-4" />
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-layout.section>
</div>

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
                    <div class="bg-white rounded-lg overflow-hidden border border-gray-200">
                        <!-- Image -->
                        <div class="aspect-square bg-gray-200">
                            <img src="{{ route($routePrefix . '.view', [$model, $image]) }}" 
                                 alt="{{ $image->alt_text ?? ucfirst($entitySingular) . ' image' }}"
                                 class="w-full h-full object-cover">
                        </div>

                        <!-- Info & Actions -->
                        <div class="p-4 space-y-3">
                            <!-- Alt Text -->
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase">Alt Text</p>
                                <p class="text-sm text-gray-900">
                                    {{ $image->alt_text ?: 'No alt text' }}
                                </p>
                            </div>

                            <!-- Display Order -->
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase">Display Order</p>
                                <p class="text-sm text-gray-900">{{ $image->display_order }}</p>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-wrap gap-2 pt-2 border-t border-gray-200">
                                <!-- Edit -->
                                <x-ui.button 
                                    href="{{ route($routePrefix . '.edit', [$model, $image]) }}" 
                                    variant="edit"
                                    size="sm"
                                    icon="pencil">
                                    Edit
                                </x-ui.button>

                                <!-- Move Up -->
                                <form method="POST" action="{{ route($routePrefix . '.move-up', [$model, $image]) }}" class="inline">
                                    @csrf
                                    <x-ui.button 
                                        type="submit"
                                        variant="ghost"
                                        size="sm"
                                        icon="arrow-up">
                                        Up
                                    </x-ui.button>
                                </form>

                                <!-- Move Down -->
                                <form method="POST" action="{{ route($routePrefix . '.move-down', [$model, $image]) }}" class="inline">
                                    @csrf
                                    <x-ui.button 
                                        type="submit"
                                        variant="ghost"
                                        size="sm"
                                        icon="arrow-down">
                                        Down
                                    </x-ui.button>
                                </form>

                                <!-- Detach -->
                                <x-ui.confirm-button 
                                    :action="route($routePrefix . '.detach', [$model, $image])"
                                    confirmMessage="Detach this image and return it to available images?"
                                    variant="warning"
                                    size="sm"
                                    icon="arrows-up-down"
                                    entity="{{ $entity }}">
                                    Detach
                                </x-ui.confirm-button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-layout.section>
</div>

{{-- Collection Translations Section --}}
<div class="mt-8">
    <x-layout.section title="Translations" icon="language">
        <x-slot:action>
            <x-ui.button 
                href="{{ route('collection-translations.create', ['collection_id' => $collection->id]) }}" 
                variant="primary" 
                entity="collections"
                icon="plus">
                Add Translation
            </x-ui.button>
        </x-slot:action>

        @if($translationsByContext->isEmpty())
            <x-ui.empty-state 
                icon="language"
                title="No translations"
                message="Get started by adding a translation for this collection.">
                <x-ui.button 
                    href="{{ route('collection-translations.create', ['collection_id' => $collection->id]) }}" 
                    variant="primary" 
                    entity="collections"
                    icon="plus">
                    Add First Translation
                </x-ui.button>
            </x-ui.empty-state>
            @else
                <div class="space-y-6">
                    @foreach($translationsByContext as $contextId => $translations)
                        @php
                            $context = $translations->first()->context;
                            $isDefaultContext = $context && $context->is_default;
                        @endphp
                        
                        {{-- Context Group Header --}}
                        <div class="border-b border-gray-200 pb-2 mb-4">
                            <h4 class="text-base font-semibold text-gray-900 flex items-center">
                                @if($isDefaultContext)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mr-2">
                                        Default
                                    </span>
                                @endif
                                <span>{{ $context ? $context->internal_name : 'No Context' }}</span>
                            </h4>
                        </div>

                        {{-- Translations in this context --}}
                        <div class="space-y-4 ml-4">
                            @foreach($translations as $translation)
                                <div class="bg-white rounded-lg overflow-hidden border border-gray-200">
                                    <div class="p-4">
                <!-- Header with Language and Context -->
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center space-x-2">
                        <x-heroicon-o-language class="h-5 w-5 text-blue-500" />
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $translation->language->internal_name ?? $translation->language_id }}
                            </span>
                            @if($translation->context)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $translation->context->internal_name }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                                <!-- Translation Title -->
                                <div class="mb-2">
                                    <h4 class="text-base font-semibold text-gray-900">
                                        {{ $translation->title }}
                                    </h4>
                                </div>

                                <!-- Description Preview -->
                                @if($translation->description)
                                    <div class="text-sm text-gray-700 mb-3 line-clamp-2">
                                        {{ Str::limit($translation->description, 200) }}
                                    </div>
                                @endif

                                <!-- Metadata -->
                                <div class="flex items-center text-xs text-gray-500 space-x-4 mb-3">
                                    @if($translation->url)
                                        <span>URL: {{ Str::limit($translation->url, 30) }}</span>
                                    @endif
                                </div>

                                <!-- Actions -->
                                <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-200">
                                    <!-- View/Edit -->
                                    <x-ui.button 
                                        href="{{ route('collection-translations.show', $translation) }}" 
                                        variant="edit"
                                        size="sm"
                                        icon="eye">
                                        View
                                    </x-ui.button>
                                    <x-ui.button 
                                        href="{{ route('collection-translations.edit', $translation) }}" 
                                        variant="warning"
                                        size="sm"
                                        icon="pencil">
                                        Edit
                                    </x-ui.button>
                                    <!-- Delete -->
                                    <form method="POST" action="{{ route('collection-translations.destroy', $translation) }}" 
                                          onsubmit="return confirm('Are you sure you want to delete this translation?');">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.button 
                                            type="submit"
                                            variant="danger"
                                            size="sm"
                                            icon="trash">
                                            Delete
                                        </x-ui.button>
                                    </form>
                                </div>
                            </div>
                        </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endif
    </x-layout.section>
</div>

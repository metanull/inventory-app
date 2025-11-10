@props([
    'entity',           // e.g., 'items', 'partners', 'collections', 'glossary'
    'model',            // The model instance
    'translationRoute', // Route prefix for translations (e.g., 'item-translations')
    'groupByContext' => true, // Whether to group by context (false for glossary)
    'primaryField' => 'name', // The main field to display (e.g., 'name', 'definition')
    'secondaryField' => null, // Optional secondary field (e.g., 'alternate_name')
    'descriptionField' => 'description', // Optional description field
])

@php
    $entitySingular = \Illuminate\Support\Str::singular($entity);
    
    // Get translations and optionally group by context
    if ($groupByContext) {
        $defaultContextId = \App\Models\Context::where('is_default', true)->value('id');
        $defaultLanguageId = \App\Models\Language::where('is_default', true)->value('id');
        
        $translationsByContext = $model->translations
            ->sortBy(function ($translation) use ($defaultLanguageId) {
                return $translation->language_id === $defaultLanguageId ? 0 : 1;
            })
            ->groupBy('context_id')
            ->sortBy(function ($group, $contextId) use ($defaultContextId) {
                if ($contextId === null) {
                    return PHP_INT_MAX;
                }
                if ($contextId === $defaultContextId) {
                    return 0;
                }
                return $contextId;
            });
    } else {
        // For glossary - no context grouping
        $translationsByContext = collect([$model->translations]);
    }
@endphp

<div class="mt-8">
    <x-layout.section title="Translations" icon="language">
        <x-slot name="action">
            <x-ui.button 
                href="{{ route($translationRoute . '.create', $entity === 'glossary' ? ['glossary' => $model->id] : [$entitySingular . '_id' => $model->id]) }}" 
                variant="primary" 
                :entity="$entity"
                icon="plus">
                Add Translation
            </x-ui.button>
        </x-slot>

        @if($translationsByContext->flatten(1)->isEmpty())
            <x-ui.empty-state 
                icon="language"
                title="No translations"
                message="Get started by adding a translation for this {{ $entitySingular }}.">
                <x-ui.button 
                    href="{{ route($translationRoute . '.create', $entity === 'glossary' ? ['glossary' => $model->id] : [$entitySingular . '_id' => $model->id]) }}" 
                    variant="primary" 
                    :entity="$entity"
                    icon="plus">
                    Add First Translation
                </x-ui.button>
            </x-ui.empty-state>
        @else
            <div class="space-y-6">
                @foreach($translationsByContext as $contextId => $translations)
                    @if($groupByContext)
                        @php
                            $context = $translations->first()->context;
                            $isDefaultContext = $context && $context->is_default;
                        @endphp
                        
                        {{-- Context Group Header --}}
                        <div class="border-b border-gray-200 pb-2 mb-4">
                            <h4 class="text-base font-semibold text-gray-900 flex items-center">
                                @if($isDefaultContext)
                                    <x-ui.badge color="green" class="mr-2">
                                        Default
                                    </x-ui.badge>
                                @endif
                                <span>{{ $context ? $context->internal_name : 'No Context' }}</span>
                            </h4>
                        </div>
                    @endif

                    {{-- Translations in this context --}}
                    <div class="space-y-4 {{ $groupByContext ? 'ml-4' : '' }}">
                        @foreach($translations as $translation)
                            <div class="bg-white rounded-lg overflow-hidden border border-gray-200">
                                <div class="p-4">
                                    <!-- Badge and Language Info -->
                                    <div class="flex items-center space-x-2 mb-2">
                                        <x-heroicon-o-language class="h-4 w-4 text-blue-500 shrink-0" />
                                        <div class="flex flex-wrap gap-2">
                                            <x-ui.badge color="blue" variant="pill">
                                                {{ $translation->language->internal_name ?? $translation->language_id }}
                                            </x-ui.badge>
                                            @if($groupByContext && $translation->context)
                                                <x-ui.badge color="gray" variant="pill">
                                                    {{ $translation->context->internal_name }}
                                                </x-ui.badge>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Title Row with Actions -->
                                    @if($translation->$primaryField ?? false)
                                        <div class="flex items-start justify-between gap-4 mb-1">
                                            <h4 class="text-base font-semibold text-gray-900 flex-1 min-w-0">
                                                {{ $translation->$primaryField }}
                                            </h4>
                                            <!-- Compact Actions on Same Line -->
                                            <div class="flex items-center gap-2 shrink-0">
                                                <a href="{{ route($translationRoute . '.show', $entity === 'glossary' ? ['glossary' => $model->id, 'translation' => $translation->id] : $translation) }}" 
                                                   class="text-gray-400 hover:text-blue-600 transition-colors" title="View">
                                                    <x-heroicon-o-eye class="w-4 h-4" />
                                                </a>
                                                <a href="{{ route($translationRoute . '.edit', $entity === 'glossary' ? ['glossary' => $model->id, 'translation' => $translation->id] : $translation) }}" 
                                                   class="text-gray-400 hover:text-yellow-600 transition-colors" title="Edit">
                                                    <x-heroicon-o-pencil class="w-4 h-4" />
                                                </a>
                                                <button type="button" 
                                                        x-data 
                                                        @click="$dispatch('confirm-action', {
                                                            title: 'Delete this translation?',
                                                            message: 'This operation cannot be undone.',
                                                            confirmLabel: 'Delete',
                                                            cancelLabel: 'Cancel',
                                                            action: '{{ route($translationRoute . '.destroy', $entity === 'glossary' ? ['glossary' => $model->id, 'translation' => $translation->id] : $translation) }}',
                                                            method: 'DELETE',
                                                            color: 'red'
                                                        })" 
                                                        class="text-gray-400 hover:text-red-600 transition-colors" title="Delete">
                                                    <x-heroicon-o-trash class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </div>
                                    @endif
                                            
                                    @if($secondaryField && ($translation->$secondaryField ?? false))
                                        <p class="text-sm text-gray-600 mb-2">
                                            {{ $translation->$secondaryField }}
                                        </p>
                                    @endif

                                    <!-- Description Preview -->
                                    @if($descriptionField && ($translation->$descriptionField ?? false))
                                        <div class="text-sm text-gray-700 mb-2 line-clamp-3 prose prose-sm">
                                            <x-display.markdown :content="Str::limit($translation->$descriptionField, 300)" />
                                        </div>
                                    @endif

                                    <!-- Metadata -->
                                    @if(($translation->type ?? false) || ($translation->dates ?? false))
                                        <div class="flex items-center text-xs text-gray-500 space-x-4">
                                            @if($translation->type ?? false)
                                                <span>Type: {{ $translation->type }}</span>
                                            @endif
                                            @if($translation->dates ?? false)
                                                <span>Dates: {{ $translation->dates }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        @endif
    </x-layout.section>
</div>

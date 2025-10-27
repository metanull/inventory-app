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
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex items-center space-x-2">
                                            <x-heroicon-o-language class="h-5 w-5 text-blue-500" />
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
                                    </div>

                                    <!-- Translation Content -->
                                    <div class="mb-2">
                                        @if($translation->$primaryField ?? false)
                                            <h4 class="text-base font-semibold text-gray-900">
                                                {{ $translation->$primaryField }}
                                            </h4>
                                        @endif
                                        
                                        @if($secondaryField && ($translation->$secondaryField ?? false))
                                            <p class="text-sm text-gray-600">
                                                {{ $translation->$secondaryField }}
                                            </p>
                                        @endif
                                    </div>

                                    <!-- Description Preview -->
                                    @if($descriptionField && ($translation->$descriptionField ?? false))
                                        <div class="text-sm text-gray-700 mb-3 line-clamp-2">
                                            {{ Str::limit($translation->$descriptionField, 200) }}
                                        </div>
                                    @endif

                                    <!-- Metadata -->
                                    <div class="flex items-center text-xs text-gray-500 space-x-4 mb-3">
                                        @if($translation->type ?? false)
                                            <span>Type: {{ $translation->type }}</span>
                                        @endif
                                        @if($translation->dates ?? false)
                                            <span>Dates: {{ $translation->dates }}</span>
                                        @endif
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-200">
                                        <!-- View/Edit -->
                                        <x-ui.button 
                                            href="{{ route($translationRoute . '.show', $entity === 'glossary' ? ['glossary' => $model->id, 'translation' => $translation->id] : $translation) }}" 
                                            variant="edit"
                                            size="sm"
                                            icon="eye">
                                            View
                                        </x-ui.button>
                                        <x-ui.button 
                                            href="{{ route($translationRoute . '.edit', $entity === 'glossary' ? ['glossary' => $model->id, 'translation' => $translation->id] : $translation) }}" 
                                            variant="warning"
                                            size="sm"
                                            icon="pencil">
                                            Edit
                                        </x-ui.button>
                                        <!-- Delete -->
                                        <x-ui.confirm-button 
                                            action="{{ route($translationRoute . '.destroy', $entity === 'glossary' ? ['glossary' => $model->id, 'translation' => $translation->id] : $translation) }}"
                                            confirmMessage="Are you sure you want to delete this translation?"
                                            variant="danger"
                                            size="sm"
                                            icon="trash">
                                            Delete
                                        </x-ui.confirm-button>
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

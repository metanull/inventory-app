{{-- Item Translations Section --}}
<div class="mt-8">
    <x-layout.section title="Translations" icon="language">
        <x-slot:action>
            <x-ui.button 
                href="{{ route('item-translations.create', ['item_id' => $item->id]) }}" 
                variant="primary" 
                entity="items"
                icon="plus">
                Add Translation
            </x-ui.button>
        </x-slot:action>

        @if($translationsByContext->isEmpty())
            <x-ui.empty-state 
                icon="language"
                title="No translations"
                message="Get started by adding a translation for this item.">
                <x-ui.button 
                    href="{{ route('item-translations.create', ['item_id' => $item->id]) }}" 
                    variant="primary" 
                    entity="items"
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
                                    <x-ui.badge color="green" class="mr-2">
                                        Default
                                    </x-ui.badge>
                                @endif
                                <span>{{ $context ? $context->internal_name : 'No Context' }}</span>
                            </h4>
                        </div>

                        {{-- Translations in this context --}}
                        <div class="space-y-4 ml-4">
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
                                                    @if($translation->context)
                                                        <x-ui.badge color="gray" variant="pill">
                                                            {{ $translation->context->internal_name }}
                                                        </x-ui.badge>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>                                <!-- Translation Name -->
                                <div class="mb-2">
                                    <h4 class="text-base font-semibold text-gray-900">
                                        {{ $translation->name }}
                                    </h4>
                                    @if($translation->alternate_name)
                                        <p class="text-sm text-gray-600">
                                            {{ $translation->alternate_name }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Description Preview -->
                                @if($translation->description)
                                    <div class="text-sm text-gray-700 mb-3 line-clamp-2">
                                        {{ Str::limit($translation->description, 200) }}
                                    </div>
                                @endif

                                <!-- Metadata -->
                                <div class="flex items-center text-xs text-gray-500 space-x-4 mb-3">
                                    @if($translation->type)
                                        <span>Type: {{ $translation->type }}</span>
                                    @endif
                                    @if($translation->dates)
                                        <span>Dates: {{ $translation->dates }}</span>
                                    @endif
                                </div>

                                <!-- Actions -->
                                <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-200">
                                    <!-- View/Edit -->
                                    <x-ui.button 
                                        href="{{ route('item-translations.show', $translation) }}" 
                                        variant="edit"
                                        size="sm"
                                        icon="eye">
                                        View
                                    </x-ui.button>
                                    <x-ui.button 
                                        href="{{ route('item-translations.edit', $translation) }}" 
                                        variant="warning"
                                        size="sm"
                                        icon="pencil">
                                        Edit
                                    </x-ui.button>
                                    <!-- Delete -->
                                    <x-ui.confirm-button 
                                        action="{{ route('item-translations.destroy', $translation) }}"
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

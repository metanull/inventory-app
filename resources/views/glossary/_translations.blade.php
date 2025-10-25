{{-- Glossary Translations Section --}}
<div class="mt-8">
    <x-layout.section title="Translations / Definitions" icon="language">
        <x-slot:action>
            <x-ui.button 
                href="{{ route('glossaries.translations.create', $glossary) }}" 
                variant="primary" 
                icon="plus">
                Add Translation
            </x-ui.button>
        </x-slot:action>

        @if($glossary->translations->isEmpty())
            <x-ui.empty-state 
                icon="language"
                title="No translations"
                message="Get started by adding a translation for this glossary entry.">
                <x-ui.button 
                    href="{{ route('glossaries.translations.create', $glossary) }}" 
                    variant="primary" 
                    icon="plus">
                    Add First Translation
                </x-ui.button>
            </x-ui.empty-state>
            @else
                <div class="space-y-4">
                    @foreach($glossary->translations as $translation)
                        <div class="bg-white rounded-lg overflow-hidden border border-gray-200">
                            <div class="p-4">
                                <!-- Header with Language -->
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center space-x-2">
                                        <x-heroicon-o-language class="h-5 w-5 text-blue-500" />
                                        <x-ui.badge color="blue" variant="pill">
                                            {{ $translation->language->internal_name ?? $translation->language_id }}
                                        </x-ui.badge>
                                    </div>
                                </div>

                                <!-- Definition -->
                                <div class="mb-3">
                                    <p class="text-sm text-gray-700">
                                        {{ $translation->definition }}
                                    </p>
                                </div>

                                <!-- Actions -->
                                <div class="flex flex-wrap gap-2 pt-3 border-t border-gray-200">
                                    <!-- View -->
                                    <x-ui.button 
                                        href="{{ route('glossaries.translations.show', [$glossary, $translation]) }}" 
                                        variant="edit"
                                        size="sm"
                                        icon="eye">
                                        View
                                    </x-ui.button>
                                    <!-- Edit -->
                                    <x-ui.button 
                                        href="{{ route('glossaries.translations.edit', [$glossary, $translation]) }}" 
                                        variant="warning"
                                        size="sm"
                                        icon="pencil">
                                        Edit
                                    </x-ui.button>
                                    <!-- Delete -->
                                    <x-ui.confirm-button 
                                        action="{{ route('glossaries.translations.destroy', [$glossary, $translation]) }}"
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
            @endif
    </x-layout.section>
</div>

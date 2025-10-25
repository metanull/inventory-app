{{-- Glossary Spellings Section --}}
<div class="mt-8">
    <x-layout.section title="Spellings" icon="document-text">
        <x-slot:action>
            <x-ui.button 
                href="{{ route('glossaries.spellings.create', $glossary) }}" 
                variant="primary" 
                icon="plus">
                Add Spelling
            </x-ui.button>
        </x-slot:action>

        @if($glossary->spellings->isEmpty())
            <x-ui.empty-state 
                icon="document-text"
                title="No spellings"
                message="Get started by adding a spelling for this glossary entry.">
                <x-ui.button 
                    href="{{ route('glossaries.spellings.create', $glossary) }}" 
                    variant="primary" 
                    icon="plus">
                    Add First Spelling
                </x-ui.button>
            </x-ui.empty-state>
        @else
                <div class="space-y-4">
                    @php
                        $spellingsByLanguage = $glossary->spellings->groupBy('language_id');
                    @endphp
                    @foreach($spellingsByLanguage as $languageId => $spellings)
                        <div class="bg-white rounded-lg overflow-hidden border border-gray-200">
                            <div class="p-4">
                                <!-- Header with Language -->
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center space-x-2">
                                        <x-heroicon-o-language class="h-5 w-5 text-green-500" />
                                        <x-ui.badge color="green" variant="pill">
                                            {{ $spellings->first()->language->internal_name ?? $languageId }}
                                        </x-ui.badge>
                                        <x-ui.badge color="gray" variant="pill" size="sm">
                                            {{ $spellings->count() }} {{ Str::plural('spelling', $spellings->count()) }}
                                        </x-ui.badge>
                                    </div>
                                </div>

                                <!-- Spellings list -->
                                <div class="mb-3 space-y-2">
                                    @foreach($spellings as $spelling)
                                        <div class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded">
                                            <p class="text-sm text-gray-700 font-mono">
                                                {{ $spelling->spelling }}
                                            </p>
                                            <div class="flex gap-2">
                                                <!-- View -->
                                                <x-ui.button 
                                                    href="{{ route('glossaries.spellings.show', [$glossary, $spelling]) }}" 
                                                    variant="edit"
                                                    size="sm"
                                                    icon="eye">
                                                    View
                                                </x-ui.button>
                                                <!-- Edit -->
                                                <x-ui.button 
                                                    href="{{ route('glossaries.spellings.edit', [$glossary, $spelling]) }}" 
                                                    variant="warning"
                                                    size="sm"
                                                    icon="pencil">
                                                    Edit
                                                </x-ui.button>
                                                <!-- Delete -->
                                                <x-ui.confirm-button 
                                                    action="{{ route('glossaries.spellings.destroy', [$glossary, $spelling]) }}"
                                                    confirmMessage="Are you sure you want to delete this spelling?"
                                                    variant="danger"
                                                    size="sm"
                                                    icon="trash">
                                                    Delete
                                                </x-ui.confirm-button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
    </x-layout.section>
</div>

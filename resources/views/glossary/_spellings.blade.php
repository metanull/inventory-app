{{-- Glossary Spellings Section --}}
<div class="mt-8">
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h2 class="text-xl font-semibold text-gray-900">Spellings</h2>
                </div>
                <div>
                    <a href="{{ route('glossaries.spellings.create', $glossary) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                        <x-heroicon-o-plus class="h-4 w-4 mr-1" />
                        Add Spelling
                    </a>
                </div>
            </div>
        </div>

        <div class="p-6">
            @if($glossary->spellings->isEmpty())
                <div class="text-center py-12 bg-gray-50 rounded-lg">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No spellings</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by adding a spelling for this glossary entry.</p>
                    <div class="mt-6">
                        <a href="{{ route('glossaries.spellings.create', $glossary) }}" 
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                            <x-heroicon-o-plus class="h-4 w-4 mr-1" />
                            Add First Spelling
                        </a>
                    </div>
                </div>
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
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ $spellings->first()->language->internal_name ?? $languageId }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $spellings->count() }} {{ Str::plural('spelling', $spellings->count()) }}
                                        </span>
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
                                                <a href="{{ route('glossaries.spellings.show', [$glossary, $spelling]) }}" 
                                                   class="inline-flex items-center px-2 py-1 bg-blue-600 border border-transparent rounded text-xs text-white hover:bg-blue-700 transition">
                                                    <x-heroicon-o-eye class="h-3 w-3" />
                                                </a>
                                                <!-- Edit -->
                                                <a href="{{ route('glossaries.spellings.edit', [$glossary, $spelling]) }}" 
                                                   class="inline-flex items-center px-2 py-1 bg-yellow-600 border border-transparent rounded text-xs text-white hover:bg-yellow-700 transition">
                                                    <x-heroicon-o-pencil class="h-3 w-3" />
                                                </a>
                                                <!-- Delete -->
                                                <form method="POST" action="{{ route('glossaries.spellings.destroy', [$glossary, $spelling]) }}" 
                                                      onsubmit="return confirm('Are you sure you want to delete this spelling?');" 
                                                      class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="inline-flex items-center px-2 py-1 bg-red-600 border border-transparent rounded text-xs text-white hover:bg-red-700 transition">
                                                        <x-heroicon-o-trash class="h-3 w-3" />
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
